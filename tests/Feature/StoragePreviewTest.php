<?php

use App\Actions\Storage\BuildStorageDisk;
use App\Models\AuditActivity;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** A saved storage whose disk is a throwaway one on this machine: the tests never touch a real storage. */
function previewDisk(): FilesystemAdapter
{
    $admin = User::factory()->superAdmin()->create();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
    StorageSetting::factory()->create();
    $disk = Storage::fake('uji-preview');
    test()->mock(BuildStorageDisk::class)->shouldReceive('forFiles')->andReturn($disk);
    AuditActivity::query()->delete();

    return $disk;
}

function previewOf(string $path): TestResponse
{
    return test()->get(route('storage.files.preview', ['type' => 's3', 'path' => $path]));
}

test('an image is shown in the page with the type taken from its extension, and the look is recorded', function () {
    $disk = previewDisk();
    $disk->put('Laporan/foto.png', 'isi-gambar');

    $response = previewOf('Laporan/foto.png')->assertOk();

    expect($response->streamedContent())->toBe('isi-gambar')
        ->and($response->headers->get('Content-Type'))->toBe('image/png')
        ->and($response->headers->get('Content-Disposition'))->toStartWith('inline')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Content-Security-Policy'))->toContain('sandbox')
        ->and(AuditActivity::query()->where('event', 'file_previewed')->count())->toBe(1);
});

test('a PDF is not sandboxed, because its viewer would not open, but nothing may load from it', function () {
    $disk = previewDisk();
    $disk->put('ringkasan.pdf', '%PDF-1.4');

    $response = previewOf('ringkasan.pdf')->assertOk();

    expect($response->headers->get('Content-Type'))->toBe('application/pdf')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("default-src 'none'")->not->toContain('sandbox');
});

test('a text file is shown as plain text even when it holds a page', function () {
    $disk = previewDisk();
    $disk->put('catatan.txt', '<script>alert(1)</script>');

    $response = previewOf('catatan.txt')->assertOk();

    expect($response->headers->get('Content-Type'))->toStartWith('text/plain');
});

test('a file whose kind may run script, or that is not on the list, is refused and not recorded', function (string $path) {
    $disk = previewDisk();
    $disk->put($path, '<svg onload="alert(1)"/>');

    previewOf($path)->assertStatus(415);

    expect(AuditActivity::query()->where('event', 'file_previewed')->count())->toBe(0);
})->with(['gambar.svg', 'halaman.html', 'arsip.zip', 'tanpa-ekstensi']);

test('a file that is too big for the screen is refused, and one that is not there is not found', function () {
    $disk = previewDisk();
    config(['storage_connections.preview_max_kb' => 1]);
    $disk->put('besar.png', str_repeat('a', 1025));

    previewOf('besar.png')->assertStatus(413);
    previewOf('hilang.png')->assertNotFound();
});

test('a path that leaves the storage is refused', function () {
    previewDisk();

    previewOf('../rahasia.png')->assertSessionHasErrors('path');
});

test('only a Super Admin can look at a file, and a guest is sent to sign in', function () {
    $this->get(route('storage.files.preview', ['type' => 's3', 'path' => 'a.png']))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('storage.files.preview', ['type' => 's3', 'path' => 'a.png']))->assertForbidden();
});

test('the file manager tells the screen which extensions it can show', function () {
    previewDisk()->put('a.png', 'x');

    $this->get(route('storage.files', 's3'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('limits.preview.png', 'image')
        ->where('limits.preview.pdf', 'pdf')
        ->where('limits.preview.txt', 'text')
        ->missing('limits.preview.svg')
        ->where('limits.preview_max_mb', 5));
});

test('looking at files is rate limited', function () {
    previewDisk()->put('a.png', 'x');

    foreach (range(1, 60) as $attempt) {
        previewOf('a.png');
    }

    previewOf('a.png')->assertTooManyRequests();
});

test('a storage that cannot be used is reported under storage and nothing is recorded', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin);
    config(['filesystems.disks.s3' => ['driver' => 's3']]);
    AuditActivity::query()->delete();

    previewOf('a.png')->assertSessionHasErrors(['storage' => 'Konfigurasi penyimpanan belum lengkap.']);

    expect(AuditActivity::query()->where('event', 'file_previewed')->count())->toBe(0);
});
