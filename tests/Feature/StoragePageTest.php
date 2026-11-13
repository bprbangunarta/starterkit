<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('storage'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create())->get(route('storage'))->assertForbidden();
});

test('a Super Admin opens the storage page', function () {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->get(route('storage'))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('storage', shouldExist: true));
});
