<?php

use App\Models\AuditActivity;
use App\Models\Office;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

beforeEach(fn () => config(['auth.default_user_password' => 'sandi-bawaan-uji']));

/** Signs in as a Super Admin and clears the entries their own creation wrote. */
function adminOnUsersPage(): User
{
    $admin = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** @return array<string, mixed> */
function newUser(array $overrides = []): array
{
    return [
        'name' => 'Rina Wulandari',
        'username' => 'rina.w',
        'email' => 'Rina.W@Example.Test',
        'roles' => [],
        'reason' => null,
        ...$overrides,
    ];
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('users'))->assertRedirect(route('login'));

    $plain = User::factory()->create();
    $victim = User::factory()->create();

    $this->actingAs($plain);
    $this->get(route('users'))->assertForbidden();
    $this->post(route('users.store'), newUser())->assertForbidden();
    $this->put(route('users.update', $victim), ['name' => 'X'])->assertForbidden();
    $this->delete(route('users.destroy', $victim))->assertForbidden();
    expect($victim->fresh()->trashed())->toBeFalse();
});

test('the list shows users with their roles and the roles to choose from', function () {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');
    User::factory()->create(['name' => 'Budi'])->assignRole('Peninjau');

    $this->get(route('users'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('users', shouldExist: true)
        ->has('users.data', 2)
        ->where('roles', ['Peninjau', 'Super Admin']));
});

test('the search finds name, username, and email with % and _ taken literally', function () {
    // The admin's own random factory data must not match the terms below.
    adminOnUsersPage()->update(['name' => 'Admin Utama', 'username' => 'admin.utama', 'email' => 'utama@kantor.test']);
    User::factory()->create(['name' => 'Ani', 'username' => 'ani_x', 'email' => 'ani@example.test']);
    User::factory()->create(['name' => 'Budi', 'username' => 'budi', 'email' => 'budi@example.test']);

    $names = fn (string $term) => collect(test()->get(route('users', ['search' => $term]))->viewData('page')['props']['users']['data'])->pluck('name')->all();

    expect($names('ani@'))->toBe(['Ani'])
        ->and($names('bud'))->toBe(['Budi'])
        ->and($names('i_x'))->toBe(['Ani'])
        ->and($names('%'))->toBe([]);
});

test('the list filters by role', function () {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');
    User::factory()->create(['name' => 'Budi'])->assignRole('Peninjau');

    $this->get(route('users', ['role' => 'Peninjau']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('users.data', 1)->where('users.data.0.name', 'Budi'));
});

test('every sort key orders the list in both directions', function (string $key, string $first, string $last) {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');
    User::factory()->create(['name' => 'Aaa', 'username' => 'aaa', 'email' => 'aaa@example.test', 'created_at' => now()->subDay()])->assignRole('Peninjau');
    User::factory()->create(['name' => 'Zzz', 'username' => 'zzz', 'email' => 'zzz@example.test', 'created_at' => now()->addDay()])->assignRole('Super Admin');

    foreach (['asc' => $first, 'desc' => $last] as $direction => $expected) {
        $page = test()->get(route('users', ['sort' => $key, 'direction' => $direction]))->viewData('page')['props']['users']['data'];
        $matching = collect($page)->pluck('name')->filter(fn (string $name) => in_array($name, ['Aaa', 'Zzz'], true))->values()->first();

        expect($matching)->toBe($expected);
    }
})->with([
    'name' => ['name', 'Aaa', 'Zzz'],
    'username' => ['username', 'Aaa', 'Zzz'],
    'email' => ['email', 'Aaa', 'Zzz'],
    'role' => ['role', 'Aaa', 'Zzz'],
    'created' => ['created', 'Aaa', 'Zzz'],
]);

test('an unknown sort key and page size fall back to the defaults', function () {
    adminOnUsersPage();

    $this->get(route('users', ['sort' => 'password', 'per_page' => 7]))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('filters.sort', 'name')->where('filters.per_page', 25));
});

test('an admin creates a user with roles, and the audit trail records the account and the roles', function () {
    $admin = adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');

    $this->post(route('users.store'), newUser(['roles' => ['Peninjau'], 'reason' => 'Petugas baru bagian peninjauan']))->assertSessionHasNoErrors();

    $user = User::query()->where('username', 'rina.w')->sole();
    expect($user->email)->toBe('rina.w@example.test')
        ->and(password_verify('sandi-bawaan-uji', $user->password))->toBeTrue()
        ->and($user->password_changed_at)->toBeNull()
        ->and($user->getRoleNames()->all())->toBe(['Peninjau']);

    $roleEntry = AuditActivity::query()->where('event', 'role_changed')->sole();
    expect($roleEntry->causer_id)->toBe($admin->id)
        ->and($roleEntry->subject_id)->toBe($user->id)
        ->and($roleEntry->attribute_changes['old'])->toBe(['roles' => []])
        ->and($roleEntry->attribute_changes['attributes'])->toBe(['roles' => ['Peninjau']])
        ->and($roleEntry->properties['reason'])->toBe('Petugas baru bagian peninjauan');
    expect(AuditActivity::query()->where('event', 'created')->where('subject_id', $user->id)->exists())->toBeTrue();
});

test('a user without roles is created and no role event is recorded', function () {
    adminOnUsersPage();

    $this->post(route('users.store'), newUser())->assertSessionHasNoErrors();

    expect(AuditActivity::query()->where('event', 'role_changed')->count())->toBe(0);
});

test('creating a user is refused for bad input', function (array $override, string $field) {
    adminOnUsersPage();
    User::factory()->create(['username' => 'dipakai', 'email' => 'dipakai@example.test']);
    Role::findOrCreate('Peninjau', 'web');

    $this->post(route('users.store'), newUser($override))->assertSessionHasErrors($field);

    expect(User::query()->where('username', 'rina.w')->exists())->toBeFalse();
})->with([
    'no name' => [['name' => ''], 'name'],
    'username with @' => [['username' => 'rina@w'], 'username'],
    'username with a space' => [['username' => 'rina w'], 'username'],
    'username taken' => [['username' => 'dipakai'], 'username'],
    'email taken in another letter case' => [['email' => 'Dipakai@Example.Test'], 'email'],
    'email invalid' => [['email' => 'bukan-email'], 'email'],
    'a password typed in' => [['password' => 'kata-sandi-yang-panjang'], 'password'],
    'unknown role' => [['roles' => ['Tidak Ada']], 'roles.0'],
]);

test('an admin changes a user and the roles, and cannot set a password this way', function () {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');
    $user = User::factory()->create(['username' => 'lama']);
    $hash = $user->password;
    AuditActivity::query()->delete();

    $this->put(route('users.update', $user), ['name' => 'Nama Baru', 'username' => 'baru', 'email' => $user->email, 'roles' => ['Peninjau'], 'reason' => 'Pindah tugas'])->assertSessionHasNoErrors();

    expect($user->fresh()->username)->toBe('baru')->and($user->fresh()->getRoleNames()->all())->toBe(['Peninjau']);
    expect(AuditActivity::query()->where('event', 'role_changed')->count())->toBe(1);

    $this->put(route('users.update', $user), ['name' => 'X', 'username' => 'baru', 'email' => $user->email, 'password' => 'kata-sandi-yang-panjang'])->assertSessionHasErrors('password');
    expect($user->fresh()->password)->toBe($hash);
});

test('saving a user with the same roles records no role event', function () {
    adminOnUsersPage();
    $user = User::factory()->create();
    AuditActivity::query()->delete();

    $this->put(route('users.update', $user), ['name' => 'Baru', 'username' => $user->username, 'email' => $user->email, 'roles' => []]);

    expect(AuditActivity::query()->where('event', 'role_changed')->count())->toBe(0);
});

test('the last Super Admin cannot lose the role, but can when another one exists', function () {
    $admin = adminOnUsersPage();
    $payload = ['name' => $admin->name, 'username' => $admin->username, 'email' => $admin->email, 'roles' => [], 'reason' => 'Uji'];

    $this->put(route('users.update', $admin), $payload)->assertSessionHasErrors('roles');
    expect($admin->fresh()->hasRole('Super Admin'))->toBeTrue();

    User::factory()->superAdmin()->create();

    $this->put(route('users.update', $admin), $payload)->assertSessionHasNoErrors();
    expect($admin->fresh()->hasRole('Super Admin'))->toBeFalse();
});

test('an admin deletes another user, who is kept in the table but can no longer be found', function () {
    adminOnUsersPage();
    $user = User::factory()->create();

    $this->delete(route('users.destroy', $user))->assertSessionHasNoErrors();

    expect(User::query()->find($user->id))->toBeNull()->and(User::withTrashed()->find($user->id))->not->toBeNull();
    expect(AuditActivity::query()->where('event', 'deleted')->where('subject_id', $user->id)->exists())->toBeTrue();
});

test('nobody deletes their own account, and the last Super Admin stays', function () {
    $admin = adminOnUsersPage();

    $this->delete(route('users.destroy', $admin))->assertSessionHasErrors('user');
    expect($admin->fresh()->trashed())->toBeFalse();

    $other = User::factory()->superAdmin()->create();
    $this->actingAs($other)->delete(route('users.destroy', $admin))->assertSessionHasNoErrors();
    expect(User::role('Super Admin')->count())->toBe(1);

    $this->actingAs(User::factory()->superAdmin()->create())->delete(route('users.destroy', $other))->assertSessionHasNoErrors();
    $last = User::role('Super Admin')->first();
    $lone = User::factory()->superAdmin()->create();
    User::query()->where('id', '!=', $lone->id)->each(fn (User $user) => $user->delete());

    $this->actingAs($lone)->delete(route('users.destroy', User::factory()->create()))->assertSessionHasNoErrors();
    expect($lone->fresh()->trashed())->toBeFalse()->and($last)->not->toBeNull();
});

test('a change of roles needs a reason, and a change of anything else does not', function () {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');
    $user = User::factory()->create();
    $payload = ['name' => 'Baru', 'username' => $user->username, 'email' => $user->email];

    $this->put(route('users.update', $user), [...$payload, 'roles' => ['Peninjau'], 'reason' => '  '])->assertSessionHasErrors('reason');
    expect($user->fresh()->getRoleNames()->all())->toBe([]);

    $this->put(route('users.update', $user), [...$payload, 'roles' => []])->assertSessionHasNoErrors();
    expect($user->fresh()->name)->toBe('Baru');
});

test('creating a user with roles also needs a reason', function () {
    adminOnUsersPage();
    Role::findOrCreate('Peninjau', 'web');

    $this->post(route('users.store'), newUser(['roles' => ['Peninjau']]))->assertSessionHasErrors('reason');

    expect(User::query()->where('username', 'rina.w')->exists())->toBeFalse();
});

test('sending the same new user twice creates one account and refuses the second', function () {
    adminOnUsersPage();

    $this->post(route('users.store'), newUser())->assertSessionHasNoErrors();
    $this->post(route('users.store'), newUser())->assertSessionHasErrors(['username', 'email']);

    expect(User::query()->where('username', 'rina.w')->count())->toBe(1);
});

test('an account that was deleted can no longer use its session', function () {
    $user = User::factory()->create(['username' => 'dihapus']);

    $this->post(route('login.store'), ['login' => 'dihapus', 'password' => 'password'])->assertRedirect(route('dashboard'));
    $this->get(route('dashboard'))->assertOk();

    $user->delete();
    auth()->forgetGuards();

    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

test('the writing routes are rate limited', function () {
    adminOnUsersPage();

    foreach (range(1, 60) as $attempt) {
        $this->post(route('users.store'), []);
    }

    $this->post(route('users.store'), [])->assertTooManyRequests();
});

test('a new account cannot be created while the default password is not set', function () {
    adminOnUsersPage();
    config(['auth.default_user_password' => null]);
    $this->withoutExceptionHandling();

    expect(fn () => $this->post(route('users.store'), newUser()))->toThrow(RuntimeException::class, 'DEFAULT_USER_PASSWORD');

    expect(User::query()->where('username', 'rina.w')->exists())->toBeFalse();
});

test('an admin resets a password: the default again, a new change required, signed out everywhere, and recorded', function () {
    $admin = adminOnUsersPage();
    $user = User::factory()->create();
    $oldHash = $user->password;
    DB::table('sessions')->insert(['id' => 'abc', 'user_id' => $user->id, 'payload' => '', 'last_activity' => time()]);
    AuditActivity::query()->delete();

    $this->post(route('users.password-reset', $user))->assertSessionHasNoErrors();

    $user->refresh();
    expect(password_verify('sandi-bawaan-uji', $user->password))->toBeTrue()
        ->and($user->password)->not->toBe($oldHash)
        ->and($user->password_changed_at)->toBeNull()
        ->and($user->passwordExpired())->toBeTrue()
        ->and(DB::table('sessions')->where('user_id', $user->id)->count())->toBe(0);
    $entry = AuditActivity::query()->where('event', 'password_reset')->sole();
    expect($entry->causer_id)->toBe($admin->id)->and($entry->subject_id)->toBe($user->id);
    expect(json_encode($entry->properties))->not->toContain('sandi-bawaan-uji');
});

test('nobody resets their own password this way, and a user without the Super Admin role cannot reset any', function () {
    $admin = adminOnUsersPage();

    $this->post(route('users.password-reset', $admin))->assertSessionHasErrors('user');

    $this->actingAs(User::factory()->create())->post(route('users.password-reset', $admin))->assertForbidden();
});

test('a password reset for a user that does not exist is not found', function () {
    adminOnUsersPage();

    $this->post(route('users.password-reset', 999999))->assertNotFound();
});

test('password resets are rate limited', function () {
    $admin = adminOnUsersPage();

    foreach (range(1, 60) as $attempt) {
        $this->post(route('users.password-reset', $admin));
    }

    $this->post(route('users.password-reset', $admin))->assertTooManyRequests();
});

test('an admin gives a user an office when creating and when changing, and takes it away again', function () {
    adminOnUsersPage();
    $north = Office::factory()->create(['name' => 'Kantor Utara']);
    $south = Office::factory()->create(['name' => 'Kantor Selatan']);

    $this->post(route('users.store'), newUser(['office_id' => $north->id]))->assertSessionHasNoErrors();
    $user = User::query()->where('username', 'rina.w')->sole();
    expect($user->office_id)->toBe($north->id);

    $this->put(route('users.update', $user), newUser(['office_id' => $south->id]))->assertSessionHasNoErrors();
    expect($user->fresh()->office_id)->toBe($south->id);
    $entry = AuditActivity::query()->where('event', 'updated')->where('subject_id', $user->id)->latest('id')->first();
    expect($entry->attribute_changes['old']['office_id'])->toBe($north->id)->and($entry->attribute_changes['attributes']['office_id'])->toBe($south->id);

    $this->put(route('users.update', $user), newUser(['office_id' => null]))->assertSessionHasNoErrors();
    expect($user->fresh()->office_id)->toBeNull();
});

test('a user may have no office, and an office that does not exist is refused', function () {
    adminOnUsersPage();

    $this->post(route('users.store'), newUser())->assertSessionHasNoErrors();
    expect(User::query()->where('username', 'rina.w')->sole()->office_id)->toBeNull();

    $this->post(route('users.store'), newUser(['username' => 'budi.s', 'email' => 'budi@example.test', 'office_id' => 9999]))->assertSessionHasErrors(['office_id' => 'Office id yang dipilih tidak valid.']);
    $this->post(route('users.store'), newUser(['username' => 'budi.s', 'email' => 'budi@example.test', 'office_id' => 'abc']))->assertSessionHasErrors('office_id');
    expect(User::query()->where('username', 'budi.s')->exists())->toBeFalse();
});

test('the list shows the office of each user and offers the offices to choose from', function () {
    adminOnUsersPage();
    $office = Office::factory()->create(['name' => 'Kantor Timur']);
    User::factory()->for($office)->create(['name' => 'Budi']);

    $this->get(route('users'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('offices', [['id' => $office->id, 'name' => 'Kantor Timur']])
        ->where('users.data', fn ($rows) => collect($rows)->firstWhere('name', 'Budi')['office'] === ['id' => $office->id, 'name' => 'Kantor Timur']));
});

test('the list sorts by office in both directions', function () {
    adminOnUsersPage();
    User::factory()->for(Office::factory()->create(['name' => 'Aaa']))->create(['name' => 'Penghuni Aaa']);
    User::factory()->for(Office::factory()->create(['name' => 'Zzz']))->create(['name' => 'Penghuni Zzz']);

    foreach (['asc' => 'Penghuni Aaa', 'desc' => 'Penghuni Zzz'] as $direction => $expected) {
        $page = test()->get(route('users', ['sort' => 'office', 'direction' => $direction]))->viewData('page')['props']['users']['data'];
        $first = collect($page)->pluck('name')->filter(fn (string $name) => str_starts_with($name, 'Penghuni'))->first();

        expect($first)->toBe($expected);
    }
});

test('the list filters by office, and by no office at all', function () {
    adminOnUsersPage()->update(['name' => 'Admin Utama']);
    $north = Office::factory()->create(['name' => 'Kantor Utara']);
    $south = Office::factory()->create(['name' => 'Kantor Selatan']);
    User::factory()->for($north)->create(['name' => 'Budi Utara']);
    User::factory()->for($south)->create(['name' => 'Sari Selatan']);

    $names = fn (array $query) => collect(test()->get(route('users', $query))->viewData('page')['props']['users']['data'])->pluck('name')->sort()->values()->all();

    expect($names(['office' => (string) $north->id]))->toBe(['Budi Utara'])
        ->and($names(['office' => (string) $south->id]))->toBe(['Sari Selatan'])
        ->and($names(['office' => 'none']))->toBe(['Admin Utama'])
        ->and($names(['office' => 'abc']))->toBe(['Admin Utama', 'Budi Utara', 'Sari Selatan'])
        ->and($names(['office' => '99999']))->toBe([]);
});

test('the office filter comes back in the filters and combines with the status filter', function () {
    adminOnUsersPage();
    $office = Office::factory()->create();
    User::factory()->for($office)->create(['name' => 'Dihapus Kantor'])->delete();

    $this->get(route('users', ['office' => (string) $office->id, 'status' => 'deleted']))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('filters.office', (string) $office->id)
        ->has('users.data', 1)
        ->where('users.data.0.name', 'Dihapus Kantor'));
});
