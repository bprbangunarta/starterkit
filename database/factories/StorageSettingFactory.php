<?php

namespace Database\Factories;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<StorageSetting>
 */
class StorageSettingFactory extends Factory
{
    /**
     * An S3 connection with made-up values.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'type' => StorageType::S3,
            'settings' => ['key' => 'AKIAEXAMPLEKEY0001', 'secret' => 'secret-contoh-bukan-asli', 'region' => 'ap-southeast-3', 'bucket' => 'bucket-contoh', 'endpoint' => null, 'use_path_style' => false, 'root' => null],
        ];
    }

    public function sftp(): static
    {
        return $this->state(['type' => StorageType::Sftp, 'settings' => ['host' => '93.184.216.34', 'port' => 22, 'username' => 'petugas', 'password' => 'sandi-contoh-bukan-asli', 'private_key' => null, 'root' => null]]);
    }

    /** The defaults of the server environment apply: nothing is saved from the screen. */
    public function usingEnvironment(): static
    {
        return $this->state(['settings' => null]);
    }
}
