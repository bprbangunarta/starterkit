<?php

use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

function adminOnRolesPage(): User
{
    $admin = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('roles'))->assertRedirect(route('login'));

    $role = Role::findOrCreate('Peninjau', 'web');
    $this->actingAs(User::factory()->create());

    $this->get(route('roles'))->assertForbidden();
    $this->post(route('roles.store'), ['name' => 'Baru'])->assertForbidden();
    $this->put(route('roles.update', $role), ['name' => 'Lain'])->assertForbidden();
    $this->delete(route('roles.destroy', $role))->assertForbidden();
    expect(Role::query()->where('name', 'Peninjau')->exists())->toBeTrue();
});

test('the list shows each role with its number of users and whether it is a base role', function () {
    adminOnRolesPage();
    Role::findOrCreate('Peninjau', 'web');

    $this->get(route('roles'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('roles', shouldExist: true)
        ->has('roles.data', 2)
        ->where('roles.data.0', fn ($role) => $role['name'] === 'Peninjau' && $role['users_count'] === 0 && $role['is_base'] === false)
        ->where('roles.data.1', fn ($role) => $role['name'] === 'Super Admin' && $role['users_count'] === 1 && $role['is_base'] === true));
});

test('an admin creates a role and it is recorded in the audit trail', function () {
    $admin = adminOnRolesPage();

    $this->post(route('roles.store'), ['name' => '  Peninjau Data '])->assertSessionHasNoErrors();

    expect(Role::query()->where('name', 'Peninjau Data')->where('guard_name', 'web')->exists())->toBeTrue();
    $entry = AuditActivity::query()->where('event', 'created')->where('log_name', 'Jabatan')->sole();
    expect($entry->causer_id)->toBe($admin->id)->and($entry->description)->toBe('Jabatan dibuat');
});

test('a role name must be new, short, and made of letters, numbers, spaces, dots, or dashes', function (string $name) {
    adminOnRolesPage();
    Role::findOrCreate('Peninjau', 'web');

    $this->post(route('roles.store'), ['name' => $name])->assertSessionHasErrors('name');
})->with(['', 'Peninjau', 'peninjau', str_repeat('a', 61), '<b>x</b>', '-awal']);

test('an admin renames a role, and the new name is recorded with the old one', function () {
    adminOnRolesPage();
    $role = Role::findOrCreate('Peninjau', 'web');

    $this->put(route('roles.update', $role), ['name' => 'Penelaah'])->assertSessionHasNoErrors();

    expect($role->fresh()->name)->toBe('Penelaah');
    $entry = AuditActivity::query()->where('event', 'updated')->where('log_name', 'Jabatan')->sole();
    expect($entry->attribute_changes['old'])->toBe(['name' => 'Peninjau'])->and($entry->attribute_changes['attributes'])->toBe(['name' => 'Penelaah']);
});

test('a base role keeps its name and cannot be deleted', function () {
    adminOnRolesPage();
    $base = Role::findByName('Super Admin', 'web');

    $this->put(route('roles.update', $base), ['name' => 'Admin'])->assertSessionHasErrors('name');
    $this->delete(route('roles.destroy', $base))->assertSessionHasErrors('role');

    expect($base->fresh()->name)->toBe('Super Admin');
});

test('a role in use cannot be deleted, and an unused one can', function () {
    adminOnRolesPage();
    $used = Role::findOrCreate('Dipakai', 'web');
    User::factory()->create()->assignRole($used);
    $free = Role::findOrCreate('Bebas', 'web');

    $this->delete(route('roles.destroy', $used))->assertSessionHasErrors('role');
    expect(Role::query()->where('name', 'Dipakai')->exists())->toBeTrue();

    $this->delete(route('roles.destroy', $free))->assertSessionHasNoErrors();
    expect(Role::query()->where('name', 'Bebas')->exists())->toBeFalse();
    expect(AuditActivity::query()->where('event', 'deleted')->where('log_name', 'Jabatan')->sole()->attribute_changes['old'])->toBe(['name' => 'Bebas']);
    expect(AuditActivity::query()->where('event', 'deleted')->where('log_name', 'Jabatan')->sole()->subject_id)->toBe($free->id);
});

test('sending the same new role twice creates one role and refuses the second', function () {
    adminOnRolesPage();

    $this->post(route('roles.store'), ['name' => 'Peninjau'])->assertSessionHasNoErrors();
    $this->post(route('roles.store'), ['name' => 'Peninjau'])->assertSessionHasErrors('name');

    expect(Role::query()->where('name', 'Peninjau')->count())->toBe(1);
});

test('the writing routes are rate limited', function () {
    adminOnRolesPage();

    foreach (range(1, 60) as $attempt) {
        $this->post(route('roles.store'), []);
    }

    $this->post(route('roles.store'), [])->assertTooManyRequests();
});

test('the role list searches by name with % and _ taken literally', function () {
    adminOnRolesPage();
    Role::findOrCreate('Peninjau', 'web');
    Role::findOrCreate('Analis_Kredit', 'web');

    $names = fn (string $term) => collect(test()->get(route('roles', ['search' => $term]))->viewData('page')['props']['roles']['data'])->pluck('name')->all();

    expect($names('penin'))->toBe(['Peninjau'])
        ->and($names('al_s'))->toBe([])
        ->and($names('is_k'))->toBe(['Analis_Kredit'])
        ->and($names('%'))->toBe([]);
});

test('every role sort key orders the list in both directions', function (string $key, string $first, string $last) {
    adminOnRolesPage();
    Role::findOrCreate('Aaa', 'web')->forceFill(['created_at' => now()->subDay()])->save();
    Role::findOrCreate('Zzz', 'web')->forceFill(['created_at' => now()->addDay()])->save();
    User::factory()->count(2)->create()->each->assignRole('Zzz');

    foreach (['asc' => $first, 'desc' => $last] as $direction => $expected) {
        $names = collect(test()->get(route('roles', ['sort' => $key, 'direction' => $direction]))->viewData('page')['props']['roles']['data'])->pluck('name');

        expect($names->filter(fn (string $name) => in_array($name, ['Aaa', 'Zzz'], true))->first())->toBe($expected);
    }
})->with([
    'name' => ['name', 'Aaa', 'Zzz'],
    'users' => ['users', 'Aaa', 'Zzz'],
    'created' => ['created', 'Aaa', 'Zzz'],
]);

test('the role list is paginated and an unknown sort key or page size falls back to the defaults', function () {
    adminOnRolesPage();
    foreach (range(1, 12) as $number) {
        Role::findOrCreate("Peranan {$number}", 'web');
    }

    $this->get(route('roles', ['per_page' => 10]))->assertInertia(fn (AssertableInertia $page) => $page->has('roles.data', 10)->where('roles.meta.total', 13));
    $this->get(route('roles', ['sort' => 'guard_name', 'per_page' => 7]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('filters.sort', 'name')->where('filters.per_page', 25)->has('roles.data', 13));
});
