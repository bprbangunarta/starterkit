<?php

namespace Database\Factories;

use App\Enums\SettingKey;
use App\Models\Setting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Setting>
 */
class SettingFactory extends Factory
{
    /** @return array<string, mixed> */
    public function definition(): array
    {
        return ['key' => SettingKey::MetaDescription->value, 'value' => fake()->sentence()];
    }

    public function appName(string $name = 'Aplikasi Contoh'): static
    {
        return $this->state(['key' => SettingKey::AppName->value, 'value' => $name]);
    }
}
