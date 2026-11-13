<?php

namespace App\Actions\Settings;

use App\Actions\Storage\ResolveStorageConfig;
use App\Enums\StorageType;
use App\Models\StorageSetting;

class ListUsableStorages
{
    public function __construct(private readonly ResolveStorageConfig $resolve) {}

    /**
     * The storages that new files may be saved to: the local one always, and the others once their connection is complete and the last
     * test succeeded.
     *
     * @return list<StorageType>
     */
    public function handle(): array
    {
        $rows = StorageSetting::query()->get()->keyBy(fn (StorageSetting $row): string => $row->type->value);

        return array_values(array_filter(StorageType::cases(), function (StorageType $type) use ($rows): bool {
            if (! $type->isConfigurable()) {
                return true;
            }

            $row = $rows->get($type->value);

            return $row?->test_ok === true && $this->resolve->handle($type, $row) !== null;
        }));
    }
}
