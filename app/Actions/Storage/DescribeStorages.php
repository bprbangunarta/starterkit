<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use App\Services\ProjectSettings;
use Illuminate\Support\Str;

class DescribeStorages
{
    public function __construct(private readonly ResolveStorageConfig $resolve, private readonly ProjectSettings $settings) {}

    /**
     * Both kinds of storage as the screen shows them. Secrets never appear: only whether they are set, and the last four characters of
     * an access key. The form is filled with the non-secret values in force.
     *
     * @return list<array<string, mixed>>
     */
    public function handle(): array
    {
        $rows = StorageSetting::query()->get()->keyBy(fn (StorageSetting $row): string => $row->type->value);

        return array_map(fn (StorageType $type): array => $this->describe($type, $rows->get($type->value)), StorageType::cases());
    }

    /** @return array<string, mixed> */
    private function describe(StorageType $type, ?StorageSetting $row): array
    {
        $resolved = $this->resolve->handle($type, $row);
        $screen = is_array($row?->settings) && $row->settings !== [];
        $disk = $resolved['disk'] ?? ($screen ? [] : (array) config("filesystems.disks.{$type->value}"));

        return [
            'type' => $type->value,
            'label' => $row->name ?? $type->label(),
            'kind' => $type->label(),
            'editable' => $type->isConfigurable(),
            'active' => $this->settings->activeStorage() === $type,
            'name' => $row?->name,
            'source' => $screen ? 'screen' : 'env',
            'configured' => $resolved !== null,
            'summary' => $resolved === null ? null : $this->summary($type, $disk),
            'form' => $this->form($type, $disk),
            'secrets' => $this->secrets($type, $disk),
            'tested_at' => $row?->tested_at?->toIso8601String(),
            'test_ok' => $row?->test_ok,
            'test_message' => $row?->test_message,
        ];
    }

    /** @param  array<string, mixed>  $disk */
    private function summary(StorageType $type, array $disk): string
    {
        return match ($type) {
            StorageType::Local => str_replace(base_path().'/', '', (string) ($disk['root'] ?? '')),
            StorageType::S3 => trim("{$disk['bucket']} · {$disk['region']}"),
            StorageType::Sftp => "{$disk['username']}@{$disk['host']}:".($disk['port'] ?? 22),
        };
    }

    /**
     * @param  array<string, mixed>  $disk
     * @return array<string, mixed>
     */
    private function form(StorageType $type, array $disk): array
    {
        return match ($type) {
            StorageType::Local => [],
            StorageType::S3 => [
                'region' => (string) ($disk['region'] ?? ''),
                'bucket' => (string) ($disk['bucket'] ?? ''),
                'endpoint' => (string) ($disk['endpoint'] ?? ''),
                'use_path_style' => (bool) ($disk['use_path_style_endpoint'] ?? false),
                'root' => (string) ($disk['root'] ?? ''),
            ],
            StorageType::Sftp => [
                'host' => (string) ($disk['host'] ?? ''),
                'port' => (string) ($disk['port'] ?? 22),
                'username' => (string) ($disk['username'] ?? ''),
                'root' => (string) ($disk['root'] ?? ''),
            ],
        };
    }

    /**
     * @param  array<string, mixed>  $disk
     * @return array<string, mixed>
     */
    private function secrets(StorageType $type, array $disk): array
    {
        return match ($type) {
            StorageType::Local => [],
            StorageType::S3 => [
                'key_hint' => filled($disk['key'] ?? null) ? '••••'.Str::substr((string) $disk['key'], -4) : null,
                'secret_set' => filled($disk['secret'] ?? null),
            ],
            StorageType::Sftp => [
                'password_set' => filled($disk['password'] ?? null),
                'private_key_set' => filled($disk['privateKey'] ?? null),
            ],
        };
    }
}
