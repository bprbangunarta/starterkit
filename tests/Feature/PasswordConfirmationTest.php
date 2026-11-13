<?php

use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/** @return list<array{string, string, array<string, mixed>}> */
function sensitiveRequests(User $target, Role $role): array
{
    $user = ['name' => 'Baru', 'username' => 'baru', 'email' => 'baru@example.test', 'roles' => [], 'reason' => null];

    return [
        ['post', route('users.store'), $user],
        ['put', route('users.update', $target), [...$user, 'username' => $target->username, 'email' => $target->email]],
        ['post', route('users.password-reset', $target), []],
        ['delete', route('users.destroy', $target), []],
        ['post', route('roles.store'), ['name' => 'Peninjau']],
        ['put', route('roles.update', $role), ['name' => 'Penelaah']],
        ['delete', route('roles.destroy', $role), []],
    ];
}

test('every sensitive action answers that a confirmation is needed (423) and does nothing until it is given', function () {
    config(['auth.default_user_password' => 'sandi-bawaan-uji']);
    $admin = User::factory()->superAdmin()->create();
    $target = User::factory()->create();
    $role = Role::findOrCreate('Dipakai', 'web');
    $this->actingAs($admin);

    foreach (sensitiveRequests($target, $role) as [$method, $url, $data]) {
        $this->{$method}($url, $data)->assertStatus(423)->assertJson(['code' => 'reauth_required']);
    }

    expect(User::query()->where('username', 'baru')->exists())->toBeFalse()
        ->and($target->fresh()->trashed())->toBeFalse()
        ->and(Role::query()->where('name', 'Peninjau')->exists())->toBeFalse()
        ->and($role->fresh()->name)->toBe('Dipakai');
});

test('guests cannot confirm a password', function () {
    $this->post(route('password.confirm.store'), ['password' => 'password'])->assertRedirect(route('login'));
});

test('the right password is confirmed and the sensitive action then works', function () {
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->from(route('roles'));

    $this->post(route('password.confirm.store'), ['password' => 'password'])->assertSessionHasNoErrors()->assertRedirect(route('roles'));

    $this->post(route('roles.store'), ['name' => 'Peninjau'])->assertSessionHasNoErrors();
    expect(Role::query()->where('name', 'Peninjau')->exists())->toBeTrue();
});

test('a wrong password is refused, recorded without the typed text, and confirms nothing', function () {
    $admin = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();
    $this->actingAs($admin);

    $this->post(route('password.confirm.store'), ['password' => 'salah-sekali'])->assertSessionHasErrors('password');

    $entry = AuditActivity::query()->where('event', 'password_confirm_failed')->sole();
    expect($entry->causer_id)->toBe($admin->id)->and(json_encode($entry->properties))->not->toContain('salah-sekali');
    $this->post(route('roles.store'), ['name' => 'Peninjau'])->assertStatus(423);
});

test('an empty password is invalid and is not recorded as a failed attempt', function () {
    $this->actingAs(User::factory()->create())->post(route('password.confirm.store'), ['password' => ''])->assertSessionHasErrors('password');

    expect(AuditActivity::query()->where('event', 'password_confirm_failed')->count())->toBe(0);
});

test('the confirmation runs out after the configured time', function () {
    $this->freezeTime();
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->post(route('password.confirm.store'), ['password' => 'password']);

    $this->travel(14)->minutes();
    $this->post(route('roles.store'), ['name' => 'Satu'])->assertSessionHasNoErrors();

    $this->travel(2)->minutes();
    $this->post(route('roles.store'), ['name' => 'Dua'])->assertStatus(423);
    expect(Role::query()->where('name', 'Dua')->exists())->toBeFalse();
});

test('the page knows until when the confirmation holds', function () {
    $this->freezeTime();
    $admin = User::factory()->superAdmin()->create();
    $this->actingAs($admin)->get(route('dashboard'))->assertInertia(fn (AssertableInertia $page) => $page->where('auth.user.confirmed_until', 0));

    $this->post(route('password.confirm.store'), ['password' => 'password']);

    $this->get(route('dashboard'))->assertInertia(fn (AssertableInertia $page) => $page->where('auth.user.confirmed_until', now()->timestamp + 900));
});

test('confirming is rate limited', function () {
    $this->actingAs(User::factory()->create());

    foreach (range(1, 6) as $attempt) {
        $this->post(route('password.confirm.store'), ['password' => 'salah']);
    }

    $this->post(route('password.confirm.store'), ['password' => 'password'])->assertTooManyRequests();
});
