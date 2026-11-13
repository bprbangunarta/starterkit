<?php

namespace App\Enums;

/** The project settings that can be changed on the Pengaturan screen. The values are what the `key` column stores. */
enum SettingKey: string
{
    case AppName = 'app_name';
    case MetaDescription = 'meta_description';
    case ActiveStorage = 'active_storage';
    case Logo = 'logo';
    case Favicon = 'favicon';

    public function label(): string
    {
        return match ($this) {
            self::AppName => 'Nama aplikasi',
            self::MetaDescription => 'Meta deskripsi',
            self::ActiveStorage => 'Penyimpanan aktif',
            self::Logo => 'Logo',
            self::Favicon => 'Favicon',
        };
    }

    /** What applies while nothing is saved for the key, so the app works with an empty table. */
    public function default(): ?string
    {
        return match ($this) {
            self::AppName => (string) config('app.name'),
            self::MetaDescription => null,
            self::ActiveStorage => StorageType::Local->value,
            self::Logo, self::Favicon => null,
        };
    }

    /** Whether the setting is an uploaded image (kept as a file in a storage) rather than a plain value. */
    public function isImage(): bool
    {
        return $this === self::Logo || $this === self::Favicon;
    }

    /** @return list<string> */
    public static function imageValues(): array
    {
        return [self::Logo->value, self::Favicon->value];
    }

    /**
     * The upload policy of an image setting: `mimes`, `max_kb`, and the `bundled` address shown while nothing is uploaded.
     *
     * @return array{mimes: list<string>, max_kb: int, bundled: string}
     */
    public function imagePolicy(): array
    {
        return config("project.images.{$this->value}");
    }
}
