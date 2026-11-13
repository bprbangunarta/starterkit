<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** @return array<string, string> */
function newPassword(string $current, string $new): array
{
    return ['current_password' => $current, 'password' => $new, 'password_confirmation' => $new];
}

test('an eight character password is accepted', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->put(route('profile.password.update'), newPassword('password', 'abcd1234'))->assertSessionHasNoErrors();

    expect(Hash::check('abcd1234', $user->fresh()->password))->toBeTrue();
});

test('changing the password starts a new lifetime and keeps only the last three in the history', function () {
    $user = User::factory()->create(['password_changed_at' => now()->subDays(29)]);
    $current = 'password';

    foreach (['sandi-satu-1', 'sandi-dua-2', 'sandi-tiga-3', 'sandi-empat-4'] as $next) {
        $this->actingAs($user->fresh())->put(route('profile.password.update'), newPassword($current, $next))->assertSessionHasNoErrors();
        $current = $next;
    }

    expect($user->fresh()->password_changed_at->isToday())->toBeTrue()
        ->and(DB::table('password_histories')->where('user_id', $user->id)->count())->toBe(3);
});

test('the new password may not equal the current one or either of the two before it, but may equal an older one', function () {
    // Seven changes in a row would meet the rate limit, which has its own test.
    $this->withoutMiddleware(ThrottleRequests::class);
    $user = User::factory()->create();
    $current = 'password';
    foreach (['sandi-satu-1', 'sandi-dua-2', 'sandi-tiga-3'] as $next) {
        $this->actingAs($user->fresh())->put(route('profile.password.update'), newPassword($current, $next));
        $current = $next;
    }

    foreach (['sandi-tiga-3', 'sandi-dua-2', 'sandi-satu-1'] as $recent) {
        $this->actingAs($user->fresh())->put(route('profile.password.update'), newPassword($current, $recent))->assertSessionHasErrors('password');
    }

    $this->actingAs($user->fresh())->put(route('profile.password.update'), newPassword($current, 'password'))->assertSessionHasNoErrors();
    expect(Hash::check('password', $user->fresh()->password))->toBeTrue();
});

test('a password older than its lifetime sends the person to change it and nowhere else', function () {
    $user = User::factory()->create(['password_changed_at' => now()->subDays(31)]);

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.expired'));
    $this->get(route('profile'))->assertRedirect(route('password.expired'));
    $this->get(route('password.expired'))->assertOk()->assertInertia(fn (AssertableInertia $page) => $page->component('password-expired', shouldExist: true)->where('first_time', false)->where('max_age_days', 30)->has('name'));
});

test('a password of 30 days is still valid and the expired page then leads to the dashboard', function () {
    $user = User::factory()->create(['password_changed_at' => now()->subDays(29)]);

    $this->actingAs($user)->get(route('dashboard'))->assertOk();
    $this->get(route('password.expired'))->assertRedirect(route('dashboard'));
});

test('an account that was given a password must change it first, and is then free to go on', function () {
    $user = User::factory()->create(['password_changed_at' => null]);

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.expired'));
    $this->get(route('password.expired'))->assertInertia(fn (AssertableInertia $page) => $page->where('first_time', true));

    $this->put(route('profile.password.update'), newPassword('password', 'sandi-pilihan-saya'))->assertSessionHasNoErrors();

    $this->get(route('dashboard'))->assertOk();
});

test('signing out stays possible while the password has expired', function () {
    $user = User::factory()->create(['password_changed_at' => null]);

    $this->actingAs($user)->post(route('logout'))->assertRedirect(route('login'));
});

test('the lifetime follows the configured number of days', function () {
    config(['auth.password_max_age_days' => 10]);
    $user = User::factory()->create(['password_changed_at' => now()->subDays(11)]);

    $this->actingAs($user)->get(route('dashboard'))->assertRedirect(route('password.expired'));
});
