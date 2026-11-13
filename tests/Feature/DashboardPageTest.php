<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('the dashboard renders the dashboard component with the app name', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('dashboard'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('dashboard', shouldExist: true)
            ->where('brand.name', config('app.name')));
});

test('guests share no authenticated user', function () {
    $this->get(route('login'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('auth.user', null));
});

test('authenticated users share only id, name, username, whether they are Super Admin, and until when their password confirmation holds', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('auth.user', [
                'id' => $user->id,
                'name' => $user->name,
                'username' => $user->username,
                'is_super_admin' => false,
                'confirmed_until' => 0,
            ]));
});

test('the UI guideline page renders its component', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('ui-guideline'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('ui-guideline', shouldExist: true));
});
