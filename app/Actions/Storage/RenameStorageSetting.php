<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Support\Facades\DB;

class RenameStorageSetting
{
    /** Gives a storage its own name, or takes it back to the name of its kind when the name is empty. The connection values are not touched. */
    public function handle(StorageType $type, ?string $name): StorageSetting
    {
        return DB::transaction(function () use ($type, $name): StorageSetting {
            $row = StorageSetting::query()->where('type', $type->value)->lockForUpdate()->first() ?? new StorageSetting(['type' => $type]);
            $row->name = $name;
            $row->save();

            return $row;
        });
    }
}
