<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Support\Facades\DB;

class SaveStorageSetting
{
    /** The values of each kind that are secrets: left empty on the form, they keep what was saved before. */
    public const SECRETS = ['s3' => ['key', 'secret'], 'sftp' => ['password', 'private_key']];

    /**
     * Saves the connection values from the screen, encrypted; from then on they are the ones in force for this kind. The result of the
     * last test is forgotten because it was about other values.
     *
     * @param  array<string, mixed>  $data
     */
    public function handle(StorageType $type, array $data): StorageSetting
    {
        return DB::transaction(function () use ($type, $data): StorageSetting {
            $row = StorageSetting::query()->where('type', $type->value)->lockForUpdate()->first() ?? new StorageSetting(['type' => $type]);
            $existing = (array) $row->settings;

            foreach (self::SECRETS[$type->value] ?? [] as $field) {
                $data[$field] = filled($data[$field] ?? null) ? $data[$field] : ($existing[$field] ?? null);
            }

            $row->settings = $data;
            $row->forceFill(['tested_at' => null, 'test_ok' => null, 'test_message' => null])->save();

            return $row;
        });
    }
}
