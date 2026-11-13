<?php

namespace App\Rules;

use App\Actions\Settings\ListUsableStorages;
use App\Enums\StorageType;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/** A storage that files may be saved to: one that is complete and passed its last connection test. */
class UsableStorage implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $type = is_string($value) ? StorageType::tryFrom($value) : null;
        $usable = array_map(fn (StorageType $item): string => $item->value, app(ListUsableStorages::class)->handle());

        if ($type === null || ! in_array($type->value, $usable, true)) {
            $fail('Penyimpanan ini belum bisa dipakai: lengkapi konfigurasinya dan uji koneksinya sampai berhasil.');
        }
    }
}
