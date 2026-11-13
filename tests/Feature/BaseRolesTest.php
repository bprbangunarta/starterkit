<?php

use App\Actions\Roles\SyncBaseRoles;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('syncing creates every base role on the default guard', function () {
    $names = array_column(RoleName::cases(), 'value');

    $created = app(SyncBaseRoles::class)->handle();

    expect($created)->toEqualCanonicalizing($names)
        ->and(Role::query()->where('guard_name', config('auth.defaults.guard'))->pluck('name')->all())->toEqualCanonicalizing($names);
});

test('syncing a second time creates nothing', function () {
    app(SyncBaseRoles::class)->handle();

    expect(app(SyncBaseRoles::class)->handle())->toBe([])
        ->and(Role::query()->count())->toBe(count(RoleName::cases()));
});

test('syncing keeps a role that already exists and creates only the missing one', function () {
    Role::create(['name' => RoleName::Guest->value, 'guard_name' => 'web']);

    expect(app(SyncBaseRoles::class)->handle())->toBe([RoleName::SuperAdmin->value])
        ->and(Role::query()->count())->toBe(2);
});

test('the sync command succeeds and says what it did', function () {
    $this->artisan('roles:sync-base')->expectsOutputToContain('Role dibuat')->assertSuccessful();
    $this->artisan('roles:sync-base')->expectsOutputToContain('sudah ada')->assertSuccessful();

    expect(Role::query()->count())->toBe(count(RoleName::cases()));
});

test('only the Super Admin role passes every gate check', function () {
    app(SyncBaseRoles::class)->handle();
    $admin = User::factory()->create()->assignRole(RoleName::SuperAdmin->value);
    $guest = User::factory()->create()->assignRole(RoleName::Guest->value);

    expect(Gate::forUser($admin)->allows('any-ability'))->toBeTrue()
        ->and(Gate::forUser($guest)->allows('any-ability'))->toBeFalse();
});

test('creating a role is logged, and a run that creates nothing is not', function () {
    Log::spy();

    app(SyncBaseRoles::class)->handle();
    app(SyncBaseRoles::class)->handle();

    Log::shouldHaveReceived('info')->once()->with('Role dasar dibuat', ['roles' => array_column(RoleName::cases(), 'value')]);
});
