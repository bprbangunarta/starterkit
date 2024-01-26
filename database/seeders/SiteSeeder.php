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
                'app_name'          => 'SIPEBRI',
                'meta_description'  => 'Sistem Pemberian Kredit BPR Bangunarta',
                'meta_keyword'      => 'bpr bangunarta, bangunarta, sipebri, login sipebri',
                'meta_image'        => null,
                'favicon'           => null,
                'logo'              => null,
                'footer_left'       => 'PT. BPR BANGUNARTA',
                'footer_right'      => 'Version 2.0.1',
            ],));

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
        }
    }
}
