<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('a signed-in page tells the screen how long the session may stay idle', function () {
    config(['session.lifetime' => 15]);

    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('session.idle_minutes', 15));
});

test('a guest page has no idle limit, so the screen never counts down', function () {
    $this->get(route('login'))->assertInertia(fn (AssertableInertia $page) => $page->where('session.idle_minutes', 0));
});
