<?php

use App\Actions\Storage\BuildStorageDisk;
use App\Models\AuditActivity;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** Signs in as a Super Admin who has confirmed the password, with a clean audit log. */
function adminOnFiles(): User
{
    $admin = User::factory()->superAdmin()->create();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** A storage connection that is saved and complete, whose disk is a throwaway one on this machine: the tests never touch a real storage. */
function fakeStorage(): FilesystemAdapter
{
    StorageSetting::factory()->create();
    $disk = Storage::fake('uji');
    test()->mock(BuildStorageDisk::class)->shouldReceive('forFiles')->andReturn($disk);
    AuditActivity::query()->delete();

    return $disk;
}

/** @return list<string> */
function listedEntryNames(array $query = []): array
{
    return collect(test()->get(route('storage.files', ['type' => 's3', ...$query]))->viewData('page')['props']['files']['data'])->pluck('name')->all();
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('storage.files', 's3'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('storage.files', 's3'))->assertForbidden();
    $this->get(route('storage.files.download', ['type' => 's3', 'path' => 'a.pdf']))->assertForbidden();
    $this->post(route('storage.folders.store', 's3'), ['name' => 'Baru'])->assertForbidden();
    $this->post(route('storage.files.store', 's3'), ['file' => UploadedFile::fake()->create('a.pdf', 10)])->assertForbidden();
    $this->patch(route('storage.files.update', 's3'), ['path' => 'a.pdf', 'name' => 'b.pdf'])->assertForbidden();
    $this->delete(route('storage.files.destroy', 's3'), ['path' => 'a.pdf'])->assertForbidden();
});

test('the writing routes ask for the password again', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    fakeStorage();

    $this->postJson(route('storage.folders.store', 's3'), ['name' => 'Baru'])->assertStatus(423);
    $this->postJson(route('storage.files.store', 's3'), ['file' => UploadedFile::fake()->create('a.pdf', 10)])->assertStatus(423);
    $this->patchJson(route('storage.files.update', 's3'), ['path' => 'a.pdf', 'name' => 'b.pdf'])->assertStatus(423);
    $this->deleteJson(route('storage.files.destroy', 's3'), ['path' => 'a.pdf'])->assertStatus(423);
});

test('the top of a storage lists folders before files with their sizes', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('zebra.txt', 'abc');
    $disk->put('Laporan/a.pdf', 'x');
    $disk->makeDirectory('Arsip');

    $this->get(route('storage.files', 's3'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('storage-files', shouldExist: true)
        ->where('storage', ['type' => 's3', 'label' => 'Amazon S3'])
        ->where('path', '')
        ->where('error', null)
        ->where('files.data.0', fn ($entry) => $entry['name'] === 'Arsip' && $entry['type'] === 'folder' && $entry['size'] === null)
        ->where('files.data.1.name', 'Laporan')
        ->where('files.data.2', fn ($entry) => $entry['name'] === 'zebra.txt' && $entry['type'] === 'file' && $entry['size'] === 3 && $entry['path'] === 'zebra.txt'));
});

test('a folder inside lists its own content', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('Laporan/2026/ringkasan.pdf', 'x');
    $disk->put('lain.txt', 'x');

    expect(listedEntryNames(['path' => 'Laporan']))->toBe(['2026'])
        ->and(listedEntryNames(['path' => 'Laporan/2026']))->toBe(['ringkasan.pdf']);
});

test('the search, the order, and the pages are applied to the folder', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('banyak.txt', str_repeat('a', 300));
    $disk->put('Cukup.pdf', 'a');
    $disk->put('abu.txt', str_repeat('a', 10));

    expect(listedEntryNames(['search' => 'U']))->toBe(['abu.txt', 'Cukup.pdf'])
        ->and(listedEntryNames(['search' => 'cukup']))->toBe(['Cukup.pdf'])
        ->and(listedEntryNames(['sort' => 'size', 'direction' => 'desc']))->toBe(['banyak.txt', 'abu.txt', 'Cukup.pdf'])
        ->and(listedEntryNames(['sort' => 'kind']))->toBe(['Cukup.pdf', 'abu.txt', 'banyak.txt'])
        ->and(listedEntryNames(['sort' => 'password']))->toBe(['abu.txt', 'banyak.txt', 'Cukup.pdf']);

    foreach (range(1, 30) as $number) {
        $disk->put(sprintf('berkas-%02d.txt', $number), 'x');
    }

    $this->get(route('storage.files', ['type' => 's3', 'per_page' => 25, 'page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('files.data', 8)->where('files.meta.total', 33)->where('files.meta.last_page', 2));
});

test('a folder bigger than the limit is cut and says so', function () {
    adminOnFiles();
    $disk = fakeStorage();
    config(['storage_connections.listing_max_entries' => 3]);
    foreach (range(1, 5) as $number) {
        $disk->put("berkas-{$number}.txt", 'x');
    }

    $this->get(route('storage.files', 's3'))->assertInertia(fn (AssertableInertia $page) => $page->where('truncated', true)->where('files.meta.total', 3));
});

test('a folder that does not exist is not found', function () {
    adminOnFiles();
    fakeStorage();

    $this->get(route('storage.files', ['type' => 's3', 'path' => 'tidak-ada']))->assertNotFound();
});

test('a place that climbs out of the storage is refused', function (string $path) {
    adminOnFiles();
    fakeStorage();

    $this->get(route('storage.files', ['type' => 's3', 'path' => $path]))->assertSessionHasErrors(['path' => 'Lokasi tidak valid.']);
    $this->deleteJson(route('storage.files.destroy', 's3'), ['path' => $path])->assertUnprocessable();
})->with(['..', '../rahasia', 'a/../../b', 'a//b', 'a/./b', '/mutlak', 'akhir/', 'a\\b']);

test('a storage that is not set up shows the reason in the page instead of failing', function () {
    adminOnFiles();
    config(['filesystems.disks.s3' => ['driver' => 's3']]);

    $this->get(route('storage.files', 's3'))->assertInertia(fn (AssertableInertia $page) => $page->where('files', null)->where('error', 'Konfigurasi penyimpanan belum lengkap.'));
});

test('a saved host inside the network is never connected to from the file manager', function () {
    adminOnFiles();
    StorageSetting::factory()->sftp()->create(['settings' => ['host' => '10.0.0.5', 'port' => 22, 'username' => 'petugas', 'password' => 'sandi', 'private_key' => null, 'root' => null]]);
    $this->mock(BuildStorageDisk::class)->shouldNotReceive('forFiles');

    $this->get(route('storage.files', 'sftp'))->assertInertia(fn (AssertableInertia $page) => $page->where('files', null)->where('error', fn ($error) => str_contains($error, 'Alamat internal tidak diizinkan')));
});

test('a failing storage says why without showing its secrets', function () {
    adminOnFiles();
    StorageSetting::factory()->create();
    $this->mock(BuildStorageDisk::class)->shouldReceive('forFiles')->andThrow(new RuntimeException('Akses ditolak untuk secret-contoh-bukan-asli'));

    $this->get(route('storage.files', 's3'))->assertInertia(fn (AssertableInertia $page) => $page->where('files', null)->where('error', 'Penyimpanan tidak dapat dipakai: Akses ditolak untuk [disembunyikan]'));
    $this->post(route('storage.folders.store', 's3'), ['name' => 'Baru'])->assertSessionHasErrors(['storage' => 'Penyimpanan tidak dapat dipakai: Akses ditolak untuk [disembunyikan]']);
});

test('an admin uploads a file into a folder, and the audit trail records it', function () {
    $admin = adminOnFiles();
    $disk = fakeStorage();
    $disk->makeDirectory('Laporan');

    $this->post(route('storage.files.store', 's3'), ['path' => 'Laporan', 'file' => UploadedFile::fake()->createWithContent('ringkasan.pdf', 'isi berkas')])->assertSessionHasNoErrors();

    expect($disk->get('Laporan/ringkasan.pdf'))->toBe('isi berkas');
    $entry = AuditActivity::query()->where('log_name', 'Penyimpanan')->where('event', 'created')->sole();
    expect($entry->causer_id)->toBe($admin->id)->and($entry->description)->toBe('Berkas diunggah')->and($entry->properties['path'])->toBe('Laporan/ringkasan.pdf');
});

test('an upload never overwrites a file, and a name from the browser cannot point outside the folder', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('ada.pdf', 'asli');

    $this->post(route('storage.files.store', 's3'), ['file' => UploadedFile::fake()->createWithContent('ada.pdf', 'baru')])
        ->assertSessionHasErrors(['file' => 'Berkas dengan nama itu sudah ada di folder ini.']);
    expect($disk->get('ada.pdf'))->toBe('asli');

    $this->post(route('storage.files.store', 's3'), ['path' => '', 'file' => UploadedFile::fake()->createWithContent('../../luar/lolos.pdf', 'x')])->assertSessionHasNoErrors();
    expect($disk->exists('lolos.pdf'))->toBeTrue()->and($disk->exists('luar'))->toBeFalse();
});

test('an upload is refused for a missing file, a big one, or a kind that is not allowed', function (array $input, string $message) {
    adminOnFiles();
    $disk = fakeStorage();

    $this->post(route('storage.files.store', 's3'), $input)->assertSessionHasErrors(['file' => $message]);

    expect($disk->allFiles())->toBe([]);
})->with([
    'no file' => [[], 'File wajib diisi.'],
    'too big' => [fn () => ['file' => UploadedFile::fake()->create('besar.pdf', 10241)], 'Ukuran berkas paling besar 10 MB.'],
    'a program' => [fn () => ['file' => UploadedFile::fake()->create('jahat.exe', 10)], 'Jenis berkas ini tidak diizinkan.'],
    'a script' => [fn () => ['file' => UploadedFile::fake()->create('jahat.php', 10)], 'Jenis berkas ini tidak diizinkan.'],
]);

test('an admin makes a folder, and a name that is taken or not valid is refused', function () {
    $admin = adminOnFiles();
    $disk = fakeStorage();
    $disk->put('berkas.txt', 'x');

    $this->post(route('storage.folders.store', 's3'), ['name' => 'Arsip'])->assertSessionHasNoErrors();
    $this->post(route('storage.folders.store', 's3'), ['path' => 'Arsip', 'name' => '2026'])->assertSessionHasNoErrors();

    expect($disk->directoryExists('Arsip/2026'))->toBeTrue()
        ->and(AuditActivity::query()->where('description', 'Folder dibuat')->count())->toBe(2)
        ->and(AuditActivity::query()->where('description', 'Folder dibuat')->first()->causer_id)->toBe($admin->id);

    $this->post(route('storage.folders.store', 's3'), ['name' => 'Arsip'])->assertSessionHasErrors(['name' => 'Nama sudah dipakai di folder ini.']);
    $this->post(route('storage.folders.store', 's3'), ['name' => 'berkas.txt'])->assertSessionHasErrors('name');
    $this->post(route('storage.folders.store', 's3'), ['name' => 'a/b'])->assertSessionHasErrors('name');
    $this->post(route('storage.folders.store', 's3'), ['name' => '..'])->assertSessionHasErrors('name');
    $this->post(route('storage.folders.store', 's3'), ['name' => ''])->assertSessionHasErrors('name');
});

test('an admin renames a file, and the audit trail keeps the old and the new path', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('Laporan/lama.pdf', 'isi');

    $this->patch(route('storage.files.update', 's3'), ['path' => 'Laporan/lama.pdf', 'name' => 'baru.pdf'])->assertSessionHasNoErrors();

    expect($disk->exists('Laporan/lama.pdf'))->toBeFalse()->and($disk->get('Laporan/baru.pdf'))->toBe('isi');
    $entry = AuditActivity::query()->where('description', 'Nama berkas diubah')->sole();
    expect($entry->attribute_changes['old'])->toBe(['path' => 'Laporan/lama.pdf'])->and($entry->attribute_changes['attributes'])->toBe(['path' => 'Laporan/baru.pdf']);
});

test('a rename is refused for a taken name, a bad name, a folder, or a file that is gone', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('a.pdf', 'a');
    $disk->put('b.pdf', 'b');
    $disk->makeDirectory('Folder');

    $this->patch(route('storage.files.update', 's3'), ['path' => 'a.pdf', 'name' => 'b.pdf'])->assertSessionHasErrors(['name' => 'Nama sudah dipakai di folder ini.']);
    $this->patch(route('storage.files.update', 's3'), ['path' => 'a.pdf', 'name' => '../b.pdf'])->assertSessionHasErrors('name');
    $this->patch(route('storage.files.update', 's3'), ['path' => 'Folder', 'name' => 'Lain'])->assertNotFound();
    $this->patch(route('storage.files.update', 's3'), ['path' => 'hilang.pdf', 'name' => 'x.pdf'])->assertNotFound();

    expect($disk->exists('a.pdf'))->toBeTrue()->and($disk->get('b.pdf'))->toBe('b');
});

test('an admin deletes a file and an empty folder, and the audit trail records both', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('Laporan/a.pdf', 'x');
    $disk->makeDirectory('Kosong');

    $this->delete(route('storage.files.destroy', 's3'), ['path' => 'Laporan/a.pdf'])->assertSessionHasNoErrors();
    $this->delete(route('storage.files.destroy', 's3'), ['path' => 'Kosong'])->assertSessionHasNoErrors();

    expect($disk->exists('Laporan/a.pdf'))->toBeFalse()->and($disk->directoryExists('Kosong'))->toBeFalse()
        ->and(AuditActivity::query()->where('event', 'deleted')->pluck('description')->sort()->values()->all())->toBe(['Berkas dihapus', 'Folder dihapus']);
});

test('a folder with things inside is not deleted, and an item that is gone or the top is not either', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('Laporan/a.pdf', 'x');

    $this->delete(route('storage.files.destroy', 's3'), ['path' => 'Laporan'])->assertSessionHasErrors(['path' => 'Folder tidak kosong. Hapus atau pindahkan isinya lebih dulu.']);
    $this->delete(route('storage.files.destroy', 's3'), ['path' => 'hilang'])->assertNotFound();
    $this->deleteJson(route('storage.files.destroy', 's3'), [])->assertUnprocessable();

    expect($disk->exists('Laporan/a.pdf'))->toBeTrue();
});

test('an admin downloads a file, which is sent as an attachment and recorded', function () {
    $admin = adminOnFiles();
    $disk = fakeStorage();
    $disk->put('Laporan/a.pdf', 'isi berkas');

    $response = $this->get(route('storage.files.download', ['type' => 's3', 'path' => 'Laporan/a.pdf']));

    $response->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
    expect($response->streamedContent())->toBe('isi berkas')
        ->and($response->headers->get('Content-Disposition'))->toContain('attachment')->toContain('a.pdf');

    $entry = AuditActivity::query()->where('event', 'file_downloaded')->sole();
    expect($entry->causer_id)->toBe($admin->id)->and($entry->properties['path'])->toBe('Laporan/a.pdf');
});

test('a download of a file that is gone or of a place outside the storage is refused', function () {
    adminOnFiles();
    fakeStorage();

    $this->get(route('storage.files.download', ['type' => 's3', 'path' => 'hilang.pdf']))->assertNotFound();
    $this->getJson(route('storage.files.download', ['type' => 's3', 'path' => '../etc/passwd']))->assertUnprocessable();
    $this->getJson(route('storage.files.download', ['type' => 's3']))->assertUnprocessable();
    expect(AuditActivity::query()->where('event', 'file_downloaded')->exists())->toBeFalse();
});

test('the files of the local storage are listed like those of any other storage, under its own name', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $disk->put('laporan.pdf', 'isi');

    $page = $this->get(route('storage.files', 'local'))->viewData('page')['props'];

    expect(collect($page['files']['data'])->pluck('name')->all())->toBe(['laporan.pdf'])
        ->and($page['storage'])->toBe(['type' => 'local', 'label' => 'Lokal']);
});

test('a file name that is too long is refused instead of cut, so the extension that was checked is the one that is kept', function () {
    adminOnFiles();
    $disk = fakeStorage();
    $name = str_repeat('a', 196).'.php'.str_repeat('b', 10).'.pdf';

    $this->post(route('storage.files.store', 's3'), ['file' => UploadedFile::fake()->createWithContent($name, 'x')])
        ->assertSessionHasErrors(['file' => 'Nama berkas paling banyak 200 karakter.']);

    expect($disk->allFiles())->toBe([]);
});

test('a storage that fails is logged once with its kind and the error class, and never with the message', function () {
    adminOnFiles();
    StorageSetting::factory()->create();
    $this->mock(BuildStorageDisk::class)->shouldReceive('forFiles')->andThrow(new RuntimeException('rahasia-host.internal gagal'));
    Log::spy();

    $this->get(route('storage.files', 's3'))->assertOk();

    Log::shouldHaveReceived('warning')->once()->with('Penyimpanan tidak dapat dipakai', ['storage' => 's3', 'error' => RuntimeException::class]);
});
