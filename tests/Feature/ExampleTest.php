<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the application returns a successful response for a signed-in user', function () {
    $this->actingAs(User::factory()->create())->get('/')->assertStatus(200);
});
