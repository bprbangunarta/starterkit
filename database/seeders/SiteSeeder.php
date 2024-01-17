<?php

namespace Database\Seeders;

use App\Models\Site;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SiteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        DB::beginTransaction();
        try {
            Site::create(array_merge([
                'app_name'          => 'Starterkit',
                'meta_description'  => 'StarterKit: Solusi pintar untuk memulai proyek dengan cepat. Dapatkan beragam fitur dan alat yang mempercepat pengembangan aplikasi Anda.',
                'meta_keyword'      => 'starterkit, laravel',
                'meta_image'        => null,
                'favicon'           => null,
                'logo'              => null,
                'footer_left'       => 'Made with ❤️ by zulfame',
                'footer_right'      => 'Documentation',
            ],));

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
