<?php

use App\Actions\Storage\BuildStorageDisk;
use App\Enums\StorageType;
use App\Models\AuditActivity;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

beforeEach(function () {
    // The tests start from a server environment with nothing set for either storage.
    config(['filesystems.disks.s3' => ['driver' => 's3'], 'filesystems.disks.sftp' => ['driver' => 'sftp']]);
});

/** Signs in as a Super Admin who has confirmed the password, with a clean audit log. */
function adminOnStoragePage(): User
{
    $admin = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** @return array<string, mixed> */
function s3Values(array $overrides = []): array
{
    return ['key' => 'AKIAEXAMPLEKEY0001', 'secret' => 'secret-contoh-bukan-asli', 'region' => 'ap-southeast-3', 'bucket' => 'bucket-contoh', 'endpoint' => '', 'use_path_style' => false, 'root' => '', ...$overrides];
}

/** @return array<string, mixed> */
function sftpValues(array $overrides = []): array
{
    return ['host' => '93.184.216.34', 'port' => 22, 'username' => 'petugas', 'password' => 'sandi-contoh-bukan-asli', 'private_key' => '', 'root' => '', ...$overrides];
}

/** @return array<string, mixed> */
function storageEntry(string $type): array
{
    return collect(test()->get(route('storage'))->viewData('page')['props']['storages'])->firstWhere('type', $type);
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('storage'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('storage'))->assertForbidden();
    $this->patch(route('storage.update', 's3'), s3Values())->assertForbidden();
    $this->delete(route('storage.destroy', 's3'))->assertForbidden();
    $this->post(route('storage.test.store', 's3'))->assertForbidden();
    expect(StorageSetting::query()->count())->toBe(0);
});

test('the writing routes ask for the password again', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    $this->patchJson(route('storage.update', 's3'), s3Values())->assertStatus(423);
    $this->deleteJson(route('storage.destroy', 's3'))->assertStatus(423);
    $this->postJson(route('storage.test.store', 's3'))->assertStatus(423);
    expect(StorageSetting::query()->count())->toBe(0);
});

test('an unknown kind of storage is not found', function () {
    adminOnStoragePage();

    $this->patch(route('storage.update', 'ftp'), s3Values())->assertNotFound();
    $this->post(route('storage.test.store', 'ftp'))->assertNotFound();
});

test('the list shows the local storage as ready, and the other kinds as not configured while the environment has nothing', function () {
    adminOnStoragePage();

    $this->get(route('storage'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('storage', shouldExist: true)
        ->has('storages', 3)
        ->where('storages.0', fn ($local) => $local['type'] === 'local' && $local['label'] === 'Lokal' && $local['editable'] === false && $local['configured'] === true && $local['summary'] === 'storage/app/private')
        ->where('storages.1', fn ($s3) => $s3['type'] === 's3' && $s3['editable'] === true && $s3['source'] === 'env' && $s3['configured'] === false && $s3['summary'] === null)
        ->where('storages.2', fn ($sftp) => $sftp['type'] === 'sftp' && $sftp['source'] === 'env' && $sftp['configured'] === false));
});

test('the local storage has no connection to set, so it can be neither saved nor reset, but it can be tested and named', function () {
    adminOnStoragePage();

    $this->patch('/storage/local', ['root' => 'x'])->assertNotFound();
    $this->delete('/storage/local')->assertNotFound();
    $this->post(route('storage.test.store', 'local'))->assertRedirect();
    $this->patch(route('storage.name.update', 'local'), ['name' => 'Berkas Proyek'])->assertRedirect();

    expect(storageEntry('local'))->toMatchArray(['label' => 'Berkas Proyek', 'kind' => 'Lokal', 'test_ok' => true]);
});

test('the defaults of the environment apply until something is saved on the screen, and no secret reaches the browser', function () {
    adminOnStoragePage();
    config(['filesystems.disks.s3' => ['driver' => 's3', 'key' => 'AKIAENVIRONMENT9999', 'secret' => 'rahasia-dari-env', 'region' => 'idn', 'bucket' => 'bucket-env']]);

    $s3 = storageEntry('s3');

    expect($s3['source'])->toBe('env')
        ->and($s3['configured'])->toBeTrue()
        ->and($s3['summary'])->toBe('bucket-env · idn')
        ->and($s3['secrets'])->toBe(['key_hint' => '••••9999', 'secret_set' => true])
        ->and($this->get(route('storage'))->getContent())->not->toContain('rahasia-dari-env')->not->toContain('AKIAENVIRONMENT9999');
});

test('saving on the screen takes over from the environment, keeps the secrets encrypted, and records the change without them', function () {
    adminOnStoragePage();
    config(['filesystems.disks.s3' => ['driver' => 's3', 'key' => 'AKIAENVIRONMENT9999', 'secret' => 'rahasia-dari-env', 'region' => 'idn', 'bucket' => 'bucket-env']]);

    $this->patch(route('storage.update', 's3'), s3Values())->assertSessionHasNoErrors();

    $entry = storageEntry('s3');
    expect($entry['source'])->toBe('screen')
        ->and($entry['summary'])->toBe('bucket-contoh · ap-southeast-3')
        ->and($entry['secrets']['key_hint'])->toBe('••••0001')
        ->and($this->get(route('storage'))->getContent())->not->toContain('secret-contoh-bukan-asli');

    expect(DB::table('storage_settings')->value('settings'))->not->toContain('secret-contoh-bukan-asli')->not->toContain('AKIAEXAMPLEKEY0001')
        ->and(StorageSetting::query()->sole()->settings['secret'])->toBe('secret-contoh-bukan-asli');

    $audit = AuditActivity::query()->where('log_name', 'Penyimpanan')->where('event', 'created')->sole();
    expect($audit->attribute_changes['attributes']['settings'])->toBe('[disembunyikan]')
        ->and(json_encode($audit->attribute_changes))->not->toContain('secret-contoh-bukan-asli');
});

test('leaving a secret empty keeps the saved one, and typing one replaces it', function () {
    adminOnStoragePage();
    $this->patch(route('storage.update', 's3'), s3Values())->assertSessionHasNoErrors();

    $this->patch(route('storage.update', 's3'), s3Values(['key' => '', 'secret' => '', 'bucket' => 'bucket-lain']))->assertSessionHasNoErrors();
    $saved = StorageSetting::query()->sole()->settings;
    expect($saved['bucket'])->toBe('bucket-lain')->and($saved['key'])->toBe('AKIAEXAMPLEKEY0001')->and($saved['secret'])->toBe('secret-contoh-bukan-asli');

    $this->patch(route('storage.update', 's3'), s3Values(['secret' => 'secret-baru-bukan-asli']))->assertSessionHasNoErrors();
    expect(StorageSetting::query()->sole()->settings['secret'])->toBe('secret-baru-bukan-asli');
});

test('an SFTP connection is saved with a password or with a private key', function () {
    adminOnStoragePage();

    $this->patch(route('storage.update', 'sftp'), sftpValues())->assertSessionHasNoErrors();
    expect(storageEntry('sftp'))->toMatchArray(['source' => 'screen', 'summary' => 'petugas@93.184.216.34:22']);

    $key = "-----BEGIN OPENSSH PRIVATE KEY-----\nabc\n-----END OPENSSH PRIVATE KEY-----";
    $this->patch(route('storage.update', 'sftp'), sftpValues(['password' => '', 'private_key' => $key]))->assertSessionHasNoErrors();
    expect(StorageSetting::query()->sole()->settings['private_key'])->toBe($key);
});

test('the first save must bring the secrets', function () {
    adminOnStoragePage();

    $this->patch(route('storage.update', 's3'), s3Values(['key' => '', 'secret' => '']))
        ->assertSessionHasErrors(['key' => 'Access key wajib diisi.', 'secret' => 'Secret key wajib diisi.']);
    $this->patch(route('storage.update', 'sftp'), sftpValues(['password' => '', 'private_key' => '']))->assertSessionHasErrors(['password' => 'Isi kata sandi atau kunci privat.']);
    expect(StorageSetting::query()->count())->toBe(0);
});

test('bad input is refused', function (string $type, array $override, string $field) {
    adminOnStoragePage();

    $values = $type === 's3' ? s3Values($override) : sftpValues($override);
    $this->patch(route('storage.update', $type), $values)->assertSessionHasErrors($field);

    expect(StorageSetting::query()->count())->toBe(0);
})->with([
    's3 without region' => ['s3', ['region' => ''], 'region'],
    's3 bucket with capitals' => ['s3', ['bucket' => 'Bucket_Contoh'], 'bucket'],
    's3 bucket too short' => ['s3', ['bucket' => 'ab'], 'bucket'],
    's3 endpoint that is not a url' => ['s3', ['endpoint' => 'ftp://contoh'], 'endpoint'],
    's3 folder that climbs out' => ['s3', ['root' => '../lain'], 'root'],
    's3 key that is too long' => ['s3', ['key' => str_repeat('a', 129)], 'key'],
    'sftp without host' => ['sftp', ['host' => ''], 'host'],
    'sftp host with a path' => ['sftp', ['host' => 'contoh.test/etc'], 'host'],
    'sftp port out of range' => ['sftp', ['port' => 70000], 'port'],
    'sftp port that is text' => ['sftp', ['port' => 'dua'], 'port'],
    'sftp without username' => ['sftp', ['username' => ''], 'username'],
    'sftp key that is not a key' => ['sftp', ['private_key' => 'bukan kunci'], 'private_key'],
    'sftp folder that climbs out' => ['sftp', ['root' => 'a/../../b'], 'root'],
]);

test('a host inside the network is refused unless it was listed, and the metadata address never', function () {
    adminOnStoragePage();

    $this->patch(route('storage.update', 'sftp'), sftpValues(['host' => '10.0.0.5']))->assertSessionHasErrors(['host' => 'Alamat internal tidak diizinkan. Daftarkan host di STORAGE_ALLOWED_HOSTS bila server itu memang dipakai.']);
    $this->patch(route('storage.update', 's3'), s3Values(['endpoint' => 'http://169.254.169.254/latest']))->assertSessionHasErrors(['endpoint' => 'Alamat layanan metadata tidak boleh dipakai.']);

    config(['storage_connections.allowed_hosts' => ['10.0.0.5', '169.254.169.254']]);

    $this->patch(route('storage.update', 'sftp'), sftpValues(['host' => '10.0.0.5']))->assertSessionHasNoErrors();
    $this->patch(route('storage.update', 's3'), s3Values(['endpoint' => 'http://169.254.169.254/latest']))->assertSessionHasErrors('endpoint');
});

test('resetting drops what was saved so the environment applies again, and resetting twice is harmless', function () {
    adminOnStoragePage();
    $this->patch(route('storage.update', 's3'), s3Values())->assertSessionHasNoErrors();
    AuditActivity::query()->delete();

    $this->delete(route('storage.destroy', 's3'))->assertSessionHasNoErrors();
    $this->delete(route('storage.destroy', 's3'))->assertSessionHasNoErrors();

    expect(storageEntry('s3')['source'])->toBe('env')
        ->and(StorageSetting::query()->sole()->settings)->toBeNull()
        ->and(AuditActivity::query()->where('log_name', 'Penyimpanan')->where('event', 'updated')->count())->toBe(1);
});

test('a test that connects is kept beside the settings and recorded', function () {
    $admin = adminOnStoragePage();
    $this->patch(route('storage.update', 's3'), s3Values())->assertSessionHasNoErrors();
    $this->mock(BuildStorageDisk::class)->shouldReceive('forTest')->once()->andReturn(Storage::fake('uji'));
    AuditActivity::query()->delete();

    $this->post(route('storage.test.store', 's3'))->assertSessionHasNoErrors();

    $entry = storageEntry('s3');
    expect($entry['test_ok'])->toBeTrue()->and($entry['test_message'])->toBe('Terhubung.')->and($entry['tested_at'])->not->toBeNull();

    $audit = AuditActivity::query()->where('event', 'storage_tested')->sole();
    expect($audit->causer_id)->toBe($admin->id)->and($audit->result->value)->toBe('succeeded')->and($audit->properties['source'])->toBe('screen');
});

test('a test that fails says why without showing the secrets', function () {
    adminOnStoragePage();
    $this->patch(route('storage.update', 's3'), s3Values())->assertSessionHasNoErrors();
    $this->mock(BuildStorageDisk::class)->shouldReceive('forTest')->once()->andThrow(new RuntimeException('Akses ditolak untuk AKIAEXAMPLEKEY0001 dengan secret-contoh-bukan-asli'));

    $this->post(route('storage.test.store', 's3'))->assertSessionHasNoErrors();

    $entry = storageEntry('s3');
    expect($entry['test_ok'])->toBeFalse()
        ->and($entry['test_message'])->toBe('Gagal terhubung: Akses ditolak untuk [disembunyikan] dengan [disembunyikan]')
        ->and(AuditActivity::query()->where('event', 'storage_tested')->sole()->result->value)->toBe('failed');
});

test('a test without enough settings does not try to connect', function () {
    adminOnStoragePage();
    $this->mock(BuildStorageDisk::class)->shouldNotReceive('forTest');

    $this->post(route('storage.test.store', 'sftp'))->assertSessionHasNoErrors();

    expect(storageEntry('sftp'))->toMatchArray(['test_ok' => false, 'test_message' => 'Konfigurasi belum lengkap.']);
});

test('a test never connects to a saved host that points inside the network', function () {
    adminOnStoragePage();
    StorageSetting::factory()->sftp()->create(['settings' => sftpValues(['host' => '10.0.0.5'])]);
    $this->mock(BuildStorageDisk::class)->shouldNotReceive('forTest');

    $this->post(route('storage.test.store', 'sftp'))->assertSessionHasNoErrors();

    expect(storageEntry('sftp')['test_message'])->toContain('Alamat internal tidak diizinkan');
});

test('the environment defaults are not held to the host check, since the operator set them', function () {
    adminOnStoragePage();
    config(['filesystems.disks.sftp' => ['driver' => 'sftp', 'host' => '10.0.0.5', 'username' => 'petugas', 'password' => 'sandi-dari-env']]);
    $this->mock(BuildStorageDisk::class)->shouldReceive('forTest')->once()->andReturn(Storage::fake('uji'));

    $this->post(route('storage.test.store', 'sftp'))->assertSessionHasNoErrors();

    expect(storageEntry('sftp')['test_ok'])->toBeTrue();
});

test('the test route is rate limited', function () {
    adminOnStoragePage();

    foreach (range(1, 10) as $attempt) {
        $this->post(route('storage.test.store', StorageType::Sftp->value));
    }

    $this->post(route('storage.test.store', StorageType::Sftp->value))->assertTooManyRequests();
});
