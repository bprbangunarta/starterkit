<?php

use App\Models\AuditActivity;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** Signs in as a Super Admin who has confirmed the password, with a clean audit log. */
function adminOnOfficesPage(): User
{
    $admin = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** @return array<string, string> */
function newOffice(array $overrides = []): array
{
    return ['name' => 'Kantor Cabang Utara', 'address' => 'Jl. Melati No. 7, Kota Utara', 'latitude' => '-6.1234567', 'longitude' => '106.7654321', ...$overrides];
}

/** @return list<string> */
function listedOffices(array $query = []): array
{
    return collect(test()->get(route('offices', $query))->viewData('page')['props']['offices']['data'])->pluck('name')->all();
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('offices'))->assertRedirect(route('login'));

    $office = Office::factory()->create();
    $this->actingAs(User::factory()->create());

    $this->get(route('offices'))->assertForbidden();
    $this->post(route('offices.store'), newOffice())->assertForbidden();
    $this->put(route('offices.update', $office), newOffice())->assertForbidden();
    $this->delete(route('offices.destroy', $office))->assertForbidden();
    expect(Office::query()->count())->toBe(1);
});

test('the writing routes ask for the password again', function () {
    $this->actingAs(User::factory()->superAdmin()->create());
    $office = Office::factory()->create();

    $this->postJson(route('offices.store'), newOffice())->assertStatus(423);
    $this->putJson(route('offices.update', $office), newOffice())->assertStatus(423);
    $this->deleteJson(route('offices.destroy', $office))->assertStatus(423);
    expect(Office::query()->count())->toBe(1);
});

test('the list shows each office with its coordinates and its number of users', function () {
    adminOnOfficesPage();
    $office = Office::factory()->create(['name' => 'Kantor Timur', 'latitude' => '-7.2500000', 'longitude' => '112.7500000']);
    User::factory()->count(2)->for($office)->create();

    $this->get(route('offices'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('offices', shouldExist: true)
        ->has('offices.data', 1)
        ->where('offices.data.0', fn ($row) => $row['name'] === 'Kantor Timur' && $row['latitude'] === '-7.2500000' && $row['longitude'] === '112.7500000' && $row['users_count'] === 2));
});

test('the search finds name and address with % and _ taken literally', function () {
    adminOnOfficesPage();
    Office::factory()->create(['name' => 'Kantor Barat', 'address' => 'Jl. Mawar 1']);
    Office::factory()->create(['name' => 'Kantor_Selatan', 'address' => 'Jl. Kenanga 2']);

    expect(listedOffices(['search' => 'mawar']))->toBe(['Kantor Barat'])
        ->and(listedOffices(['search' => 'or_s']))->toBe(['Kantor_Selatan'])
        ->and(listedOffices(['search' => 'or s']))->toBe([])
        ->and(listedOffices(['search' => '%']))->toBe([]);
});

test('every sort key orders the list in both directions', function (string $key) {
    adminOnOfficesPage();
    Office::factory()->create(['name' => 'Aaa', 'address' => 'Aaa', 'latitude' => '-9.0000000', 'longitude' => '100.0000000', 'created_at' => now()->subDay()]);
    Office::factory()->create(['name' => 'Zzz', 'address' => 'Zzz', 'latitude' => '4.0000000', 'longitude' => '130.0000000', 'created_at' => now()->addDay()]);
    User::factory()->count(2)->for(Office::query()->where('name', 'Zzz')->sole())->create();

    expect(listedOffices(['sort' => $key, 'direction' => 'asc']))->toBe(['Aaa', 'Zzz'])
        ->and(listedOffices(['sort' => $key, 'direction' => 'desc']))->toBe(['Zzz', 'Aaa']);
})->with(['name', 'address', 'latitude', 'longitude', 'users', 'created']);

test('the list is paginated and an unknown sort key or page size falls back to the defaults', function () {
    adminOnOfficesPage();
    Office::factory()->count(12)->create();

    $this->get(route('offices', ['per_page' => 10]))->assertInertia(fn (AssertableInertia $page) => $page->has('offices.data', 10)->where('offices.meta.total', 12));
    $this->get(route('offices', ['sort' => 'password', 'per_page' => 7]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('filters.sort', 'name')->where('filters.per_page', 25)->has('offices.data', 12));
});

test('an admin creates an office, and the audit trail records its values', function () {
    $admin = adminOnOfficesPage();

    $this->post(route('offices.store'), newOffice())->assertSessionHasNoErrors();

    $office = Office::query()->sole();
    expect($office->name)->toBe('Kantor Cabang Utara')
        ->and($office->latitude)->toBe('-6.1234567')
        ->and($office->longitude)->toBe('106.7654321');

    $entry = AuditActivity::query()->where('event', 'created')->where('log_name', 'Kantor')->sole();
    expect($entry->causer_id)->toBe($admin->id)
        ->and($entry->subject_id)->toBe($office->id)
        ->and($entry->attribute_changes['attributes']['name'])->toBe('Kantor Cabang Utara');
});

test('an office is refused for bad input', function (array $override, string $field, string $message) {
    adminOnOfficesPage();
    Office::factory()->create(['name' => 'Sudah Ada']);

    $this->post(route('offices.store'), newOffice($override))->assertSessionHasErrors([$field => $message]);

    expect(Office::query()->count())->toBe(1);
})->with([
    'no name' => [['name' => ''], 'name', 'Nama wajib diisi.'],
    'a name already used' => [['name' => 'Sudah Ada'], 'name', 'Nama sudah ada sebelumnya.'],
    'a long name' => [['name' => str_repeat('a', 101)], 'name', 'Nama maksimal berisi 100 karakter.'],
    'a long address' => [['address' => str_repeat('a', 256)], 'address', 'Alamat maksimal berisi 255 karakter.'],
    'a longitude without a latitude' => [['latitude' => ''], 'latitude', 'Latitude wajib diisi bila terdapat longitude.'],
    'a latitude without a longitude' => [['longitude' => ''], 'longitude', 'Longitude wajib diisi bila terdapat latitude.'],
    'a latitude past the pole' => [['latitude' => '90.0000001'], 'latitude', 'Latitude harus bernilai antara -90 sampai 90.'],
    'a latitude out of range' => [['latitude' => '91'], 'latitude', 'Latitude harus bernilai antara -90 sampai 90.'],
    'a latitude with letters' => [['latitude' => '1e5'], 'latitude', 'Format latitude tidak valid.'],
    'a latitude with eight decimals' => [['latitude' => '1.12345678'], 'latitude', 'Format latitude tidak valid.'],
    'a longitude out of range' => [['longitude' => '180.5'], 'longitude', 'Longitude harus bernilai antara -180 sampai 180.'],
    'a longitude with a comma' => [['longitude' => '106,8'], 'longitude', 'Format longitude tidak valid.'],
]);

test('only the name is required: an office without address and coordinates is created, and so is a second one', function () {
    adminOnOfficesPage();

    $this->post(route('offices.store'), ['name' => 'Kantor Baru Satu', 'address' => '', 'latitude' => '', 'longitude' => ''])->assertSessionHasNoErrors();
    $this->post(route('offices.store'), ['name' => 'Kantor Baru Dua'])->assertSessionHasNoErrors();

    $office = Office::query()->where('name', 'Kantor Baru Satu')->sole();
    expect(Office::query()->count())->toBe(2)
        ->and($office->address)->toBeNull()
        ->and($office->latitude)->toBeNull()
        ->and($office->longitude)->toBeNull();
});

test('no two offices share a pair of coordinates, whatever the number of decimals typed', function () {
    adminOnOfficesPage();
    Office::factory()->create(['name' => 'Kantor Asal', 'latitude' => '-6.1200000', 'longitude' => '106.8000000']);

    $this->post(route('offices.store'), newOffice(['name' => 'Kantor Kembar', 'latitude' => '-6.12', 'longitude' => '106.8']))
        ->assertSessionHasErrors(['latitude' => 'Koordinat ini sudah dipakai kantor lain.']);
    $this->post(route('offices.store'), newOffice(['name' => 'Kantor Sebelah', 'latitude' => '-6.12', 'longitude' => '106.8000001']))->assertSessionHasNoErrors();

    expect(Office::query()->where('name', 'Kantor Kembar')->exists())->toBeFalse()->and(Office::query()->count())->toBe(2);
});

test('an office keeps its own coordinates when it is saved, and cannot take those of another office', function () {
    adminOnOfficesPage();
    $first = Office::factory()->create(['latitude' => '-6.2000000', 'longitude' => '106.8000000']);
    $second = Office::factory()->create(['latitude' => '-7.2000000', 'longitude' => '112.7000000']);

    $this->put(route('offices.update', $first), newOffice(['name' => $first->name, 'latitude' => '-6.2', 'longitude' => '106.8']))->assertSessionHasNoErrors();
    $this->put(route('offices.update', $second), newOffice(['name' => $second->name, 'latitude' => '-6.2', 'longitude' => '106.8']))
        ->assertSessionHasErrors('latitude');

    expect($second->fresh()->latitude)->toBe('-7.2000000');
});

test('an admin clears the address and coordinates of an office', function () {
    adminOnOfficesPage();
    $office = Office::factory()->create();

    $this->put(route('offices.update', $office), ['name' => $office->name, 'address' => '', 'latitude' => '', 'longitude' => ''])->assertSessionHasNoErrors();

    $fresh = $office->fresh();
    expect($fresh->address)->toBeNull()->and($fresh->latitude)->toBeNull()->and($fresh->longitude)->toBeNull();
});

test('the list shows an office without address and coordinates, and every sort key still works', function () {
    adminOnOfficesPage();
    Office::factory()->withoutLocation()->create(['name' => 'Kantor Kosong']);
    Office::factory()->create(['name' => 'Kantor Lengkap']);

    expect(listedOffices(['sort' => 'latitude', 'direction' => 'asc']))->toBe(['Kantor Kosong', 'Kantor Lengkap'])
        ->and(listedOffices(['sort' => 'address', 'direction' => 'desc']))->toBe(['Kantor Lengkap', 'Kantor Kosong']);
});

test('the edge coordinates are accepted', function () {
    adminOnOfficesPage();

    $this->post(route('offices.store'), newOffice(['name' => 'Ujung Utara', 'latitude' => '90', 'longitude' => '-180']))->assertSessionHasNoErrors();
    $this->post(route('offices.store'), newOffice(['name' => 'Ujung Selatan', 'latitude' => '-90.0000000', 'longitude' => '180']))->assertSessionHasNoErrors();

    expect(Office::query()->count())->toBe(2);
});

test('an admin changes an office, keeps its own name, and the audit trail records the old and new values', function () {
    adminOnOfficesPage();
    $office = Office::factory()->create(['name' => 'Kantor Lama', 'address' => 'Jl. Lama 1']);
    AuditActivity::query()->delete();

    $this->put(route('offices.update', $office), newOffice(['name' => 'Kantor Lama', 'address' => 'Jl. Baru 2']))->assertSessionHasNoErrors();

    expect($office->fresh()->address)->toBe('Jl. Baru 2');

    $entry = AuditActivity::query()->where('event', 'updated')->where('log_name', 'Kantor')->sole();
    expect($entry->attribute_changes['old']['address'])->toBe('Jl. Lama 1')
        ->and($entry->attribute_changes['attributes']['address'])->toBe('Jl. Baru 2');
});

test('an office cannot take the name of another one, and an unknown office is not found', function () {
    adminOnOfficesPage();
    Office::factory()->create(['name' => 'Kantor Satu']);
    $other = Office::factory()->create(['name' => 'Kantor Dua']);

    $this->put(route('offices.update', $other), newOffice(['name' => 'Kantor Satu']))->assertSessionHasErrors('name');
    $this->put(route('offices.update', 9999), newOffice())->assertNotFound();
    $this->delete(route('offices.destroy', 9999))->assertNotFound();

    expect($other->fresh()->name)->toBe('Kantor Dua');
});

test('an office without users is deleted and recorded, and one with users or deleted users stays', function () {
    adminOnOfficesPage();
    $free = Office::factory()->create(['name' => 'Kantor Bebas']);
    $used = Office::factory()->create();
    $formerly = Office::factory()->create();
    User::factory()->for($used)->create();
    User::factory()->for($formerly)->create()->delete();

    $this->delete(route('offices.destroy', $used))->assertSessionHasErrors('office');
    $this->delete(route('offices.destroy', $formerly))->assertSessionHasErrors('office');
    expect(Office::query()->count())->toBe(3);

    $this->delete(route('offices.destroy', $free))->assertSessionHasNoErrors();

    expect(Office::query()->whereKey($free->id)->exists())->toBeFalse();
    $entry = AuditActivity::query()->where('event', 'deleted')->where('log_name', 'Kantor')->sole();
    expect($entry->subject_id)->toBe($free->id)->and($entry->attribute_changes['old']['name'])->toBe('Kantor Bebas');
});

test('sending the same new office twice creates one and refuses the second', function () {
    adminOnOfficesPage();

    $this->post(route('offices.store'), newOffice())->assertSessionHasNoErrors();
    $this->post(route('offices.store'), newOffice())->assertSessionHasErrors('name');

    expect(Office::query()->count())->toBe(1);
});

test('the writing routes are rate limited', function () {
    adminOnOfficesPage();

    foreach (range(1, 60) as $attempt) {
        $this->post(route('offices.store'), []);
    }

    $this->post(route('offices.store'), [])->assertTooManyRequests();
});

test('an audit entry of an office names its subject Kantor', function () {
    adminOnOfficesPage();
    $this->post(route('offices.store'), newOffice())->assertSessionHasNoErrors();

    $this->get(route('audit-logs'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('logs.data.0.module', 'Kantor')
        ->where('logs.data.0.subject.type', 'Kantor'));
});
