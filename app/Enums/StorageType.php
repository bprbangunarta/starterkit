<?php

namespace App\Enums;

/** The kinds of file storage the app can show. The values are also the names of the Laravel disks whose `.env` values are the defaults. */
enum StorageType: string
{
    case Local = 'local';
    case S3 = 's3';
    case Sftp = 'sftp';

    public function label(): string
    {
        return match ($this) {
            self::Local => 'Lokal',
            self::S3 => 'Amazon S3',
            self::Sftp => 'SFTP',
        };
    }

    /** Whether the connection can be set on the screen. The local storage is the project's own folder, so there is nothing to connect to. */
    public function isConfigurable(): bool
    {
        return $this !== self::Local;
    }

    /** @return list<string> */
    public static function configurableValues(): array
    {
        return array_values(array_map(fn (self $type): string => $type->value, array_filter(self::cases(), fn (self $type): bool => $type->isConfigurable())));
    }

    /**
     * The host a disk of this kind connects to, empty when it connects to none.
     *
     * @param  array<string, mixed>  $disk
     */
    public function hostOf(array $disk): string
    {
        return match ($this) {
            self::Local => '',
            self::S3 => (string) parse_url((string) ($disk['endpoint'] ?? ''), PHP_URL_HOST),
            self::Sftp => (string) ($disk['host'] ?? ''),
        };
    }
}
