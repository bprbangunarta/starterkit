<?php

use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Auth\Events\OtherDeviceLogout;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

const NEW_PASSWORD = 'kata-sandi-baru-yang-panjang';

/** @return array<string, string> */
function passwordChange(string $current = 'password', string $new = NEW_PASSWORD, ?string $confirmation = null): array
{
    return [
        'current_password' => $current,
        'password' => $new,
        'password_confirmation' => $confirmation ?? $new,
    ];
}

test('guests cannot open the profile or change a password', function () {
    $this->get(route('profile'))->assertRedirect(route('login'));
    $this->put(route('profile.password.update'), passwordChange())->assertRedirect(route('login'));
});

test('the profile shows the account of the signed-in user and nothing else', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('profile'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('profile', shouldExist: true)
            ->where('account', ['name' => $user->name, 'username' => $user->username, 'email' => $user->email]));
});

test('a user changes their password and stays signed in', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->put(route('profile.password.update'), passwordChange())
        ->assertSessionHasNoErrors();

    expect(Hash::check(NEW_PASSWORD, $user->fresh()->password))->toBeTrue();
    $this->assertAuthenticatedAs($user);
});

test('a session that still holds the old password is signed out after the change', function () {
    $user = User::factory()->create();
    $oldHash = $user->password;

    $this->actingAs($user)->put(route('profile.password.update'), passwordChange());

    $this->flushSession()
        ->actingAs($user->fresh())
        ->withSession(['password_hash_web' => $oldHash])
        ->get(route('profile'))
        ->assertRedirect(route('login'));
});

test('changing the password is recorded once, without the password or its hash', function () {
    $user = User::factory()->create();
    $oldHash = $user->password;
    AuditActivity::query()->delete();

    $this->actingAs($user)->put(route('profile.password.update'), passwordChange());

    $entry = AuditActivity::query()->where('event', 'updated')->where('subject_id', $user->id)->sole();
    expect($entry->attribute_changes['attributes']['password'])->toBe('[disembunyikan]')
        ->and(json_encode($entry->attribute_changes))->not->toContain(NEW_PASSWORD)->not->toContain($oldHash);
});

test('a wrong current password changes nothing', function () {
    $user = User::factory()->create();
    $hash = $user->password;

    $this->actingAs($user)
        ->put(route('profile.password.update'), passwordChange(current: 'salah-sekali'))
        ->assertSessionHasErrors('current_password');

    expect($user->fresh()->password)->toBe($hash);
});

test('a wrong current password is recorded as a refused security event without any password', function () {
    $user = User::factory()->create();
    AuditActivity::query()->delete();

    $this->actingAs($user)->put(route('profile.password.update'), passwordChange(current: 'salah-sekali'));

    $entry = AuditActivity::query()->where('event', 'password_change_failed')->sole();
    expect($entry->result->value)->toBe('refused')
        ->and($entry->causer_id)->toBe($user->id)
        ->and($entry->subject_id)->toBe($user->id)
        ->and(json_encode($entry->properties))->not->toContain('salah-sekali')->not->toContain(NEW_PASSWORD);
});

test('a form error on the new password is not recorded as a security event', function () {
    $this->actingAs(User::factory()->create())->put(route('profile.password.update'), passwordChange(new: 'pendek'));

    expect(AuditActivity::query()->where('event', 'password_change_failed')->count())->toBe(0);
});

test('a new password shorter than eight characters is refused', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('profile.password.update'), passwordChange(new: 'pendek'))
        ->assertSessionHasErrors('password');
});

test('the new password may not equal the current one', function () {
    $this->actingAs(User::factory()->create(['password' => 'kata-sandi-lama-yang-panjang']))
        ->put(route('profile.password.update'), passwordChange(current: 'kata-sandi-lama-yang-panjang', new: 'kata-sandi-lama-yang-panjang'))
        ->assertSessionHasErrors('password');
});

test('the confirmation must match', function () {
    $this->actingAs(User::factory()->create())
        ->put(route('profile.password.update'), passwordChange(confirmation: 'beda-dengan-kata-sandi-baru'))
        ->assertSessionHasErrors('password');
});

test('password changes are rate limited', function () {
    $user = User::factory()->create();

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($user)->put(route('profile.password.update'), passwordChange(current: 'salah-sekali'));
    }

    $this->actingAs($user)->put(route('profile.password.update'), passwordChange())->assertTooManyRequests();
});

test('audit logging of the user stays on when ending the other sessions fails', function () {
    $user = User::factory()->create(['username' => 'lama']);
    Event::listen(OtherDeviceLogout::class, fn () => throw new RuntimeException('sesi lain gagal diakhiri'));
    $this->withoutExceptionHandling();

    expect(fn () => $this->actingAs($user)->put(route('profile.password.update'), passwordChange()))
        ->toThrow(RuntimeException::class);

    expect(Hash::check(NEW_PASSWORD, $user->fresh()->password))->toBeTrue();

    AuditActivity::query()->delete();
    $user->update(['username' => 'baru']);

    expect(AuditActivity::query()->where('event', 'updated')->where('subject_id', $user->id)->count())->toBe(1);
});
