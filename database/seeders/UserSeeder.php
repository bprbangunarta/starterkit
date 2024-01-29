<?php

namespace Database\Seeders;

use App\Models\User;
use Spatie\Permission\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Str;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $default_user_value = [
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ];

        DB::beginTransaction();

        try {
            $admin1 = User::create(array_merge([
                'name'      => 'Zulfadli Rizal',
                'username'  => 'zulfame',
                'phone'     => '6282320099971',
                'email'     => 'zulfadlirizal@gmail.com',
                'is_active' => '1',
            ], $default_user_value));

            $admin2 = User::create(array_merge([
                'name'      => 'Apip',
                'username'  => 'apip',
                'phone'     => '6285221561458',
                'email'     => 'apipsasa7@gmail.com',
                'is_active' => '1',
            ], $default_user_value));

            $admin3 = User::create(array_merge([
                'name'      => 'Yandi Rosyandi',
                'username'  => 'yandi',
                'phone'     => '6281242758273',
                'email'     => 'yandiyandhi1294@gmail.com',
                'is_active' => '1',
            ], $default_user_value));

            $guest = User::create(array_merge([
                'name'      => 'Guest',
                'username'  => 'guest',
                'phone'     => '628112051232',
                'email'     => 'guest@gmail.com',
                'is_active' => '1',
            ], $default_user_value));

            Role::create(['name' => 'Administrator']);
            Role::create(['name' => 'Guest']);

            $admin1->assignRole('Administrator');
            $admin2->assignRole('Administrator');
            $admin3->assignRole('Administrator');
            $guest->assignRole('Guest');

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
