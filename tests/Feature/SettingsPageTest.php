<?php

use App\Models\AuditActivity;
use App\Models\Setting;
use App\Models\StorageSetting;
use App\Models\User;
use App\Services\ProjectSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** Signs in as a Super Admin who has confirmed the password. */
function adminOnSettingsPage(): User
{
    $admin = User::factory()->superAdmin()->create();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);

    return $admin;
}

/** @return array<string, string> */
function settingsValues(array $overrides = []): array
{
    return ['app_name' => 'STARTERKIT Dua', 'meta_description' => 'Kerangka aplikasi Laravel.', 'active_storage' => 'local', ...$overrides];
}

test('guests are sent to the login page and a user without the Super Admin role is refused', function () {
    $this->get(route('settings'))->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());

    $this->get(route('settings'))->assertForbidden();
    $this->patch(route('settings.update'), settingsValues())->assertForbidden();
    expect(Setting::query()->count())->toBe(0);
});

test('the settings page shows the defaults while nothing is saved, and offers only the storages that can be used', function () {
    adminOnSettingsPage();

    $this->get(route('settings'))->assertInertia(fn (AssertableInertia $page) => $page
        ->component('settings', shouldExist: true)
        ->where('settings.app_name', config('app.name'))
        ->where('settings.meta_description', '')
        ->where('settings.active_storage', 'local')
        ->where('storages', [['value' => 'local', 'label' => 'Lokal']]));
});

test('a storage is offered once its connection passed the test', function () {
    adminOnSettingsPage();
    StorageSetting::factory()->create(['test_ok' => true, 'tested_at' => now()]);

    $this->get(route('settings'))->assertInertia(fn (AssertableInertia $page) => $page->has('storages', 2)->where('storages.1.value', 's3'));
});

test('saving changes the name and description that every page shows, and is recorded', function () {
    adminOnSettingsPage();
    AuditActivity::query()->delete();

    $this->patch(route('settings.update'), settingsValues())->assertRedirect();

    expect(app(ProjectSettings::class)->appName())->toBe('STARTERKIT Dua');
    $this->get(route('settings'))->assertSee('STARTERKIT Dua</title>', false)->assertSee('<meta name="description" content="Kerangka aplikasi Laravel.">', false);
    expect(AuditActivity::query()->where('log_name', 'Pengaturan')->count())->toBeGreaterThanOrEqual(2);
});

test('an empty description takes the setting back to having none', function () {
    adminOnSettingsPage();
    $this->patch(route('settings.update'), settingsValues());

    $this->patch(route('settings.update'), settingsValues(['meta_description' => '']))->assertRedirect();

    $this->get(route('settings'))->assertDontSee('name="description"', false);
});

test('the settings are checked', function (array $overrides, string $field) {
    adminOnSettingsPage();

    $this->patch(route('settings.update'), settingsValues($overrides))->assertSessionHasErrors($field);
    expect(Setting::query()->count())->toBe(0);
})->with([
    'no name' => [['app_name' => ''], 'app_name'],
    'a name that is too long' => [['app_name' => str_repeat('a', 61)], 'app_name'],
    'a description that is too long' => [['meta_description' => str_repeat('a', 161)], 'meta_description'],
    'an unknown storage' => [['active_storage' => 'ftp'], 'active_storage'],
    'a storage that was never tested' => [['active_storage' => 's3'], 'active_storage'],
]);

test('a storage whose last test failed cannot be made active', function () {
    adminOnSettingsPage();
    StorageSetting::factory()->create(['test_ok' => false, 'tested_at' => now()]);

    $this->patch(route('settings.update'), settingsValues(['active_storage' => 's3']))->assertSessionHasErrors('active_storage');
});

test('a storage that passed its test can be made active, and the list shows it as active', function () {
    adminOnSettingsPage();
    StorageSetting::factory()->create(['test_ok' => true, 'tested_at' => now()]);

    $this->patch(route('settings.update'), settingsValues(['active_storage' => 's3']))->assertSessionHasNoErrors();

    $this->get(route('storage'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('storages.0.active', false)
        ->where('storages.1.active', true));
});

test('saving settings asks for the password again', function () {
    $this->actingAs(User::factory()->superAdmin()->create());

    $this->patchJson(route('settings.update'), settingsValues())->assertStatus(423);
    expect(Setting::query()->count())->toBe(0);
});
