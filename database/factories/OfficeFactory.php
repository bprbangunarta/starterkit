<?php

namespace Database\Factories;

use App\Models\Office;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Office>
 */
class OfficeFactory extends Factory
{
    /**
     * Made-up offices somewhere in Indonesia.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => 'Kantor '.fake()->unique()->city(),
            'address' => fake()->streetAddress(),
            'latitude' => number_format(fake()->latitude(-10, 5), 7, '.', ''),
            'longitude' => number_format(fake()->longitude(96, 140), 7, '.', ''),
        ];
    }

    /** An office whose address and coordinates are not known yet. */
    public function withoutLocation(): static
    {
        return $this->state(['address' => null, 'latitude' => null, 'longitude' => null]);
    }
}
