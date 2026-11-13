<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('an unknown address shows the styled 404 page of the app', function () {
    $this->get('/tidak-ada')
        ->assertNotFound()
        ->assertSee('Halaman tidak ditemukan')
        ->assertSee('Kode 404')
        ->assertSee('/logo.png', false);
});

test('a user without the Super Admin role sees the styled 403 page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('users'))
        ->assertForbidden()
        ->assertSee('Akses ditolak')
        ->assertSee('Kode 403');
});
