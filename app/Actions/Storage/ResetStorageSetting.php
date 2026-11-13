<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Support\Facades\DB;

class ResetStorageSetting
{
    /** Drops what was saved on the screen, so the defaults of the server environment are in force again. */
    public function handle(StorageType $type): void
    {
        DB::transaction(function () use ($type): void {
            $row = StorageSetting::query()->where('type', $type->value)->lockForUpdate()->first();

            if ($row === null || $row->settings === null) {
                return;
            }

            $row->forceFill(['settings' => null, 'tested_at' => null, 'test_ok' => null, 'test_message' => null])->save();
        });
    }
}
