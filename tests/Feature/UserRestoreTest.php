<?php

use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** Signs in as a Super Admin who has confirmed the password, with a clean audit log. */
function adminForRestore(): User
{
    $admin = User::factory()->superAdmin()->create(['name' => 'Admin Utama', 'username' => 'admin.utama', 'email' => 'utama@kantor.test']);
    AuditActivity::query()->delete();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** @return list<string> */
function listedNames(string $status): array
{
    $query = $status === 'default' ? [] : ['status' => $status];

    return collect(test()->get(route('users', $query))->viewData('page')['props']['users']['data'])->pluck('name')->sort()->values()->all();
}

test('the list shows active users by default, deleted ones on request, and both for all', function () {
    adminForRestore();
    User::factory()->create(['name' => 'Aktif']);
    User::factory()->create(['name' => 'Dihapus'])->delete();

    expect(listedNames('default'))->toBe(['Admin Utama', 'Aktif'])
        ->and(listedNames('active'))->toBe(['Admin Utama', 'Aktif'])
        ->and(listedNames('deleted'))->toBe(['Dihapus'])
        ->and(listedNames('all'))->toBe(['Admin Utama', 'Aktif', 'Dihapus']);
});

test('an unknown status falls back to active users and each row says when it was deleted', function () {
    adminForRestore();
    User::factory()->create(['name' => 'Dihapus'])->delete();

    expect(listedNames('hapus'))->toBe(['Admin Utama']);

    $this->get(route('users', ['status' => 'deleted']))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('filters.status', 'deleted')
        ->where('users.data.0.deleted_at', fn ($at) => is_string($at)));
});

test('the status column sorts deleted users apart from active ones', function () {
    adminForRestore();
    User::factory()->create(['name' => 'Dihapus'])->delete();

    $first = fn (string $direction) => collect(test()->get(route('users', ['status' => 'all', 'sort' => 'status', 'direction' => $direction]))->viewData('page')['props']['users']['data'])->pluck('name')->first();

    expect($first('desc'))->toBe('Dihapus')->and($first('asc'))->not->toBe('Dihapus');
});

test('an admin restores a deleted user, who is active again and recorded in the audit trail', function () {
    adminForRestore();
    $user = User::factory()->create();
    $user->delete();

    $this->post(route('users.restore', $user))->assertSessionHasNoErrors();

    expect($user->fresh()->trashed())->toBeFalse()
        ->and(AuditActivity::query()->where('event', 'restored')->where('subject_id', $user->id)->exists())->toBeTrue();
});

test('only a deleted user can be restored', function () {
    adminForRestore();
    $user = User::factory()->create();

    $this->post(route('users.restore', $user))->assertSessionHasErrors('user');
    expect(AuditActivity::query()->where('event', 'restored')->exists())->toBeFalse();
});

test('a deleted user cannot be changed, reset, or deleted again until restored', function () {
    adminForRestore();
    $user = User::factory()->create();
    $user->delete();

    $this->put(route('users.update', $user), ['name' => 'X'])->assertNotFound();
    $this->post(route('users.password-reset', $user))->assertNotFound();
    $this->delete(route('users.destroy', $user))->assertNotFound();
});

test('guests, users without the Super Admin role, and unconfirmed admins cannot restore', function () {
    $user = User::factory()->create();
    $user->delete();

    $this->post(route('users.restore', $user))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->post(route('users.restore', $user))->assertForbidden();

    $this->actingAs(User::factory()->superAdmin()->create())->postJson(route('users.restore', $user))->assertStatus(423);
    expect($user->fresh()->trashed())->toBeTrue();
});

test('a restored user keeps the roles from before the deletion', function () {
    adminForRestore();
    $user = User::factory()->superAdmin()->create();
    $user->delete();

    $this->post(route('users.restore', $user))->assertSessionHasNoErrors();

    expect($user->fresh()->hasRole('Super Admin'))->toBeTrue();
});
