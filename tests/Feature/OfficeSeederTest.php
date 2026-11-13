<?php

use App\Models\Office;
use Database\Seeders\DatabaseSeeder;
use Database\Seeders\OfficeSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('the office seeder adds its offices, with coordinates in range', function () {
    $this->seed(OfficeSeeder::class);

    expect(Office::query()->count())->toBeGreaterThan(0);
    Office::query()->each(function (Office $office): void {
        expect((float) $office->latitude)->toBeBetween(-90, 90)->and((float) $office->longitude)->toBeBetween(-180, 180);
    });
});

test('seeding twice does not duplicate an office and never overwrites one changed in the app', function () {
    $this->seed(OfficeSeeder::class);
    $office = Office::query()->firstOrFail();
    $office->update(['address' => 'Alamat yang diubah di aplikasi']);

    $this->seed(OfficeSeeder::class);

    expect(Office::query()->where('name', $office->name)->count())->toBe(1)
        ->and($office->fresh()->address)->toBe('Alamat yang diubah di aplikasi');
});

test('the main seeder includes the offices', function () {
    $this->seed(DatabaseSeeder::class);

    expect(Office::query()->count())->toBeGreaterThan(0);
});
