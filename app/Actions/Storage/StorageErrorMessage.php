<?php

namespace App\Actions\Storage;

use Illuminate\Support\Str;
use Throwable;

class StorageErrorMessage
{
    /**
     * A sentence about a failed call to a storage. An error can quote what was sent, so the values of the connection are cut out of it.
     *
     * @param  array<string, mixed>  $disk
     */
    public function handle(string $prefix, Throwable $error, array $disk): string
    {
        $secrets = array_filter(
            [$disk['key'] ?? null, $disk['secret'] ?? null, $disk['password'] ?? null, $disk['privateKey'] ?? null],
            fn (mixed $value): bool => is_string($value) && $value !== '',
        );

        return Str::of(str_replace($secrets, '[disembunyikan]', $prefix.$error->getMessage()))->squish()->limit(240, '')->toString();
    }
}
