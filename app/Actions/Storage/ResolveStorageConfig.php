<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;

class ResolveStorageConfig
{
    /**
     * The connection that is in force for a kind of storage: what was saved on the screen when there is something, otherwise the
     * defaults of the server environment. Null when that is not enough to connect.
     *
     * @return array{source: 'screen'|'env', disk: array<string, mixed>}|null
     */
    public function handle(StorageType $type, ?StorageSetting $row): ?array
    {
        $screen = $row?->settings;
        $fromScreen = is_array($screen) && $screen !== [];
        $disk = $fromScreen ? $this->diskFromScreen($type, $screen) : (array) config("filesystems.disks.{$type->value}");

        return $this->isComplete($type, $disk) ? ['source' => $fromScreen ? 'screen' : 'env', 'disk' => $disk] : null;
    }

    /**
     * @param  array<string, mixed>  $settings
     * @return array<string, mixed>
     */
    private function diskFromScreen(StorageType $type, array $settings): array
    {
        return match ($type) {
            StorageType::Local => (array) config('filesystems.disks.local'),
            StorageType::S3 => [
                'driver' => 's3',
                'key' => $settings['key'] ?? null,
                'secret' => $settings['secret'] ?? null,
                'region' => $settings['region'] ?? null,
                'bucket' => $settings['bucket'] ?? null,
                'endpoint' => $settings['endpoint'] ?? null,
                'use_path_style_endpoint' => (bool) ($settings['use_path_style'] ?? false),
                'root' => (string) ($settings['root'] ?? ''),
            ],
            StorageType::Sftp => [
                'driver' => 'sftp',
                'host' => $settings['host'] ?? null,
                'port' => (int) ($settings['port'] ?? 22),
                'username' => $settings['username'] ?? null,
                'password' => $settings['password'] ?? null,
                'privateKey' => $settings['private_key'] ?? null,
                'root' => (string) ($settings['root'] ?? ''),
            ],
        };
    }

    /** @param  array<string, mixed>  $disk */
    private function isComplete(StorageType $type, array $disk): bool
    {
        return match ($type) {
            StorageType::Local => true,
            StorageType::S3 => filled($disk['key'] ?? null) && filled($disk['secret'] ?? null) && filled($disk['region'] ?? null) && filled($disk['bucket'] ?? null),
            StorageType::Sftp => filled($disk['host'] ?? null) && filled($disk['username'] ?? null) && (filled($disk['password'] ?? null) || filled($disk['privateKey'] ?? null)),
        };
    }
}
