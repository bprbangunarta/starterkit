<?php

namespace Database\Seeders;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Log;

class UserSeeder extends Seeder
{
    /**
     * Fictional local accounts: the factory's default password and a reserved `.test` domain.
     *
     * @var list<array{name: string, username: string, email: string, role: RoleName}>
     */
    private const ACCOUNTS = [
        ['name' => 'Super Admin', 'username' => 'superadmin', 'email' => 'superadmin@example.test', 'role' => RoleName::SuperAdmin],
        ['name' => 'Guest', 'username' => 'guest', 'email' => 'guest@example.test', 'role' => RoleName::Guest],
    ];

    /** Off by default (SEED_DEMO_ACCOUNTS), so production never gets accounts with a known password. */
    public function run(): void
    {
        if (! config('app.seed_demo_accounts')) {
            Log::info('Akun demo dilewati: SEED_DEMO_ACCOUNTS tidak aktif.');

            return;
        }

        foreach (self::ACCOUNTS as $account) {
            $user = User::query()->firstWhere('username', $account['username'])
                ?? User::factory()->create([
                    'name' => $account['name'],
                    'username' => $account['username'],
                    'email' => $account['email'],
                ]);

            $user->assignRole($account['role']->value);
        }
    }
}
