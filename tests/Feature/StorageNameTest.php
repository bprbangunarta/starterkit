<?php

use App\Models\AuditActivity;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function adminWhoConfirmedPassword(): User
{
    $admin = User::factory()->superAdmin()->create();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

function listedLabel(string $type): string
{
    return collect(test()->get(route('storage'))->viewData('page')['props']['storages'])->firstWhere('type', $type)['label'];
}

test('a storage can be given a name, which the list then shows', function () {
    adminWhoConfirmedPassword();

    $this->patch(route('storage.name.update', 's3'), ['name' => '  Arsip Kantor  '])->assertRedirect();

    expect(StorageSetting::query()->where('type', 's3')->sole()->name)->toBe('Arsip Kantor')
        ->and(listedLabel('s3'))->toBe('Arsip Kantor')
        ->and(listedLabel('sftp'))->toBe('SFTP');
});

test('an empty name takes the storage back to the name of its kind', function () {
    adminWhoConfirmedPassword();
    $this->patch(route('storage.name.update', 's3'), ['name' => 'Arsip Kantor']);

    $this->patch(route('storage.name.update', 's3'), ['name' => ''])->assertRedirect();

    expect(StorageSetting::query()->where('type', 's3')->sole()->name)->toBeNull()
        ->and(listedLabel('s3'))->toBe('Amazon S3');
});

test('two storages cannot share a name, but a storage can keep its own', function () {
    adminWhoConfirmedPassword();
    $this->patch(route('storage.name.update', 's3'), ['name' => 'Arsip']);

    $this->patch(route('storage.name.update', 'sftp'), ['name' => 'Arsip'])->assertSessionHasErrors(['name' => 'Nama ini sudah dipakai penyimpanan lain.']);
    $this->patch(route('storage.name.update', 's3'), ['name' => 'Arsip'])->assertSessionHasNoErrors();

    expect(StorageSetting::query()->where('type', 'sftp')->exists())->toBeFalse();
});

test('a name longer than 100 characters is refused', function () {
    adminWhoConfirmedPassword();

    $this->patch(route('storage.name.update', 's3'), ['name' => str_repeat('a', 101)])->assertSessionHasErrors('name');
});

test('renaming leaves a trace in the audit log', function () {
    adminWhoConfirmedPassword();
    AuditActivity::query()->delete();

    $this->patch(route('storage.name.update', 's3'), ['name' => 'Arsip Kantor']);

    expect(AuditActivity::query()->where('log_name', 'Penyimpanan')->count())->toBe(1);
});

test('only a Super Admin who just confirmed the password can rename, and only a known kind', function () {
    $this->patch(route('storage.name.update', 's3'), ['name' => 'X'])->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());
    $this->patch(route('storage.name.update', 's3'), ['name' => 'X'])->assertForbidden();

    $this->actingAs(User::factory()->superAdmin()->create());
    $this->patchJson(route('storage.name.update', 's3'), ['name' => 'X'])->assertStatus(423);

    adminWhoConfirmedPassword();
    $this->patch(route('storage.name.update', 'ftp'), ['name' => 'X'])->assertNotFound();

    expect(StorageSetting::query()->count())->toBe(0);
});
