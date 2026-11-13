<?php

use App\Http\Controllers\ErrorPreviewController;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('guests are sent to the login page', function () {
    $this->get(route('ui-guideline.errors.show', 503))->assertRedirect(route('login'));
});

test('a signed-in user previews every error screen the app words', function (int $status) {
    $this->actingAs(User::factory()->create())
        ->get(route('ui-guideline.errors.show', $status))
        ->assertInertia(fn (AssertableInertia $page) => $page->component('error-preview', shouldExist: true)->where('status', $status));
})->with(ErrorPreviewController::STATUSES);

test('a status the app does not word is not found', function () {
    $this->actingAs(User::factory()->create())->get('/ui-guideline/errors/418')->assertNotFound();
});
