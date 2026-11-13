<?php

namespace App\Services;

use App\Enums\SettingKey;
use App\Enums\StorageType;
use App\Models\Setting;
use Illuminate\Support\Facades\Cache;

/** Reads the project settings: what was saved on the Pengaturan screen, else the default of the key. Every page asks, so the rows are cached as one map. */
class ProjectSettings
{
    private const CACHE_KEY = 'project_settings';

    public function value(SettingKey $key): ?string
    {
        $stored = $this->stored()[$key->value] ?? null;

        return filled($stored) ? $stored : $key->default();
    }

    public function appName(): string
    {
        return (string) $this->value(SettingKey::AppName);
    }

    /** The storage that new files are saved to; the local one until another is chosen. */
    public function activeStorage(): StorageType
    {
        return StorageType::tryFrom((string) $this->value(SettingKey::ActiveStorage)) ?? StorageType::Local;
    }

    /**
     * Where an uploaded image is kept: the storage it was saved to, which may no longer be the active one, and its path there.
     *
     * @return array{storage: StorageType, path: string}|null
     */
    public function image(SettingKey $key): ?array
    {
        $stored = json_decode((string) $this->value($key), true);
        $storage = is_array($stored) ? StorageType::tryFrom((string) ($stored['storage'] ?? '')) : null;

        return $storage === null || ! is_string($stored['path'] ?? null) ? null : ['storage' => $storage, 'path' => $stored['path']];
    }

    /** The address of an uploaded image, with a version so a replaced image is fetched again; null while the bundled one applies. */
    public function imageUrl(SettingKey $key): ?string
    {
        return $this->image($key) === null ? null : route('brand.image', $key->value).'?v='.hash('crc32b', (string) $this->value($key));
    }

    /** Called after a setting is saved, so the next read sees it. */
    public function forget(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /** @return array<string, string|null> */
    private function stored(): array
    {
        return Cache::rememberForever(self::CACHE_KEY, fn (): array => Setting::query()->pluck('value', 'key')->all());
    }
}
