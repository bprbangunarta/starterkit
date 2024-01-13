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

        \App\Models\Site::factory()->create([
            'app_name'          => 'Starterkit',
            'meta_description'  => 'StarterKit: Solusi pintar untuk memulai proyek dengan cepat. Dapatkan beragam fitur dan alat yang mempercepat pengembangan aplikasi Anda.',
            'meta_keyword'      => 'starterkit, laravel',
            'meta_image'        => '',
            'favicon'           => '',
            'logo'              => '',
            'footer_left'       => '© 2024 , made with ❤️ by Pixinvent',
            'footer_right'      => 'Documentation',
        ]);
    }
}
