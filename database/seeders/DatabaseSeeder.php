<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        \App\Models\User::factory()->create([
            'name'      => 'Zulfadli Rizal',
            'username'  => 'zulfame',
            'email'     => 'zulfadlirizal@gmail.com',
            'password'  => '$2y$10$9eHZErbQBjrnbril5XSaOugpDjhfzehuTDvGyvVUEuf2DKeotyaT6',
            'is_active' => '1',
        ]);
    }
}
