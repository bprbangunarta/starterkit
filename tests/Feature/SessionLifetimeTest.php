<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('a signed-in session older than the absolute lifetime is ended', function () {
    $startedAt = now()->subMinutes((int) config('session.absolute_lifetime') + 1)->getTimestamp();

    $this->actingAs(User::factory()->create())
        ->withSession(['auth_started_at' => $startedAt])
        ->get(route('dashboard'))
        ->assertRedirect(route('login'))
        ->assertSessionHas('message', 'Sesi Anda berakhir. Silakan masuk lagi.');

    $this->assertGuest();
});

test('a session inside the absolute lifetime keeps working however active it is', function () {
    $startedAt = now()->subMinutes((int) config('session.absolute_lifetime') - 1)->getTimestamp();

    $this->actingAs(User::factory()->create())
        ->withSession(['auth_started_at' => $startedAt])
        ->get(route('dashboard'))
        ->assertOk();
});

test('the first request of a signed-in session starts its clock', function () {
    $this->freezeTime();

    $this->actingAs(User::factory()->create())->get(route('dashboard'));

    expect(session('auth_started_at'))->toBe(now()->getTimestamp());
});

test('guests are not affected by the absolute lifetime', function () {
    $this->get(route('login'))->assertOk();
});
