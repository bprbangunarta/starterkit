<?php

use App\Models\User;
use App\Notifications\AuditExportFailed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

test('guests share an empty bell', function () {
    $this->get(route('login'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('notifications', ['unread' => 0, 'items' => []]));
});

test('the bell shows the unread count and the notices of the signed-in user only', function () {
    $user = User::factory()->create();
    $user->notify(new AuditExportFailed);
    User::factory()->create()->notify(new AuditExportFailed);

    $this->actingAs($user)->get(route('dashboard'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('notifications.unread', 1)
            ->has('notifications.items', 1)
            ->where('notifications.items.0.level', 'warning')
            ->where('notifications.items.0.read', false));
});

test('a notice is marked read by its owner', function () {
    $user = User::factory()->create();
    $user->notify(new AuditExportFailed);
    $notice = $user->notifications()->sole();

    $this->actingAs($user)->post(route('notifications.read', $notice->id))->assertRedirect();

    expect($notice->fresh()->read_at)->not->toBeNull();
});

test('a notice of someone else is not found', function () {
    $owner = User::factory()->create();
    $owner->notify(new AuditExportFailed);

    $this->actingAs(User::factory()->create())->post(route('notifications.read', $owner->notifications()->sole()->id))->assertNotFound();

    expect($owner->unreadNotifications()->count())->toBe(1);
});

test('all notices are marked read at once, for the owner only', function () {
    $user = User::factory()->create();
    $other = User::factory()->create();
    $user->notify(new AuditExportFailed);
    $user->notify(new AuditExportFailed);
    $other->notify(new AuditExportFailed);

    $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();

    expect($user->unreadNotifications()->count())->toBe(0)
        ->and($other->unreadNotifications()->count())->toBe(1);
});
