<?php

namespace Database\Seeders;

use App\Models\Office;
use Illuminate\Database\Seeder;

class OfficeSeeder extends Seeder
{
    /**
     * Sample office. TODO: replace with the seven real offices (name, address, latitude, longitude).
     *
     * @var list<array{name: string, address: string, latitude: string, longitude: string}>
     */
    private const OFFICES = [
        ['name' => 'Kantor Pusat', 'address' => 'Jl. Contoh No. 1, Kota Contoh', 'latitude' => '-6.2000000', 'longitude' => '106.8166667'],
    ];

    /** Adds the offices that are missing by name and never overwrites one that was changed in the app. */
    public function run(): void
    {
        foreach (self::OFFICES as $office) {
            Office::query()->firstOrCreate(['name' => $office['name']], $office);
        }
    }
}
