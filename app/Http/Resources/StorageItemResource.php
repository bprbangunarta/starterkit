<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** One folder or file of a storage, as the file manager lists it. The resource wraps an array made by ListStorageFolder. */
class StorageItemResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        /** @var array<string, mixed> $entry */
        $entry = $this->resource;

        return [
            'name' => $entry['name'],
            'path' => $entry['path'],
            'type' => $entry['type'],
            'size' => $entry['size'],
            'modified' => $entry['modified'],
        ];
    }
}
