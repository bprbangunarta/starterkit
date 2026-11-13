<?php

use App\Enums\RoleName;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

test('demo accounts are skipped unless they are switched on, but the roles are still created', function () {
    config(['app.seed_demo_accounts' => false]);

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(0)
        ->and(Role::query()->count())->toBe(count(RoleName::cases()));
});

test('demo accounts get their roles when switched on', function () {
    config(['app.seed_demo_accounts' => true]);

    $this->seed(DatabaseSeeder::class);

    expect(User::role(RoleName::SuperAdmin->value)->count())->toBe(1)
        ->and(User::role(RoleName::Guest->value)->count())->toBe(1);
});

test('seeding the demo accounts twice does not duplicate them', function () {
    config(['app.seed_demo_accounts' => true]);

    $this->seed(DatabaseSeeder::class);
    $this->seed(DatabaseSeeder::class);

    expect(User::query()->count())->toBe(2);
});

test('demo accounts use a reserved test domain, never a real address', function () {
    config(['app.seed_demo_accounts' => true]);

    $this->seed(DatabaseSeeder::class);

    expect(User::query()->pluck('email')->every(fn (string $email) => str_ends_with($email, '@example.test')))->toBeTrue();
});

test('the demo accounts have the factory default password', function () {
    config(['app.seed_demo_accounts' => true]);

    $this->seed(DatabaseSeeder::class);

    expect(Hash::check('password', User::query()->where('username', 'superadmin')->value('password')))->toBeTrue();
});
