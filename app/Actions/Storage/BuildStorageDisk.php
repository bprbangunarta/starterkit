<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Storage;
use LogicException;

class BuildStorageDisk
{
    /**
     * A disk for the connection test: short time limits, so a wrong host fails quickly.
     *
     * @param  array<string, mixed>  $config
     */
    public function forTest(StorageType $type, array $config): FilesystemAdapter
    {
        return $this->build($type, $config, (int) config('storage_connections.timeout'));
    }

    /**
     * A disk for the file manager: the same connect limit, but a longer total limit because a file takes longer than a listing.
     *
     * @param  array<string, mixed>  $config
     */
    public function forFiles(StorageType $type, array $config): FilesystemAdapter
    {
        return $this->build($type, $config, (int) config('storage_connections.file_timeout'));
    }

    /**
     * Errors are thrown instead of swallowed: the caller must see why a call failed.
     *
     * @param  array<string, mixed>  $config
     */
    private function build(StorageType $type, array $config, int $total): FilesystemAdapter
    {
        $connect = (int) config('storage_connections.connect_timeout');

        $limits = match ($type) {
            StorageType::Local => [],
            StorageType::S3 => ['http' => ['connect_timeout' => $connect, 'timeout' => $total]],
            StorageType::Sftp => ['timeout' => $connect, 'maxTries' => 1],
        };

        $disk = Storage::build([...$config, ...$limits, 'throw' => true]);
        if (! $disk instanceof FilesystemAdapter) {
            throw new LogicException('A storage disk must be a filesystem adapter.');
        }

        return $disk;
    }
}
