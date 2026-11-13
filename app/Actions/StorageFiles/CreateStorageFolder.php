<?php

namespace App\Actions\StorageFiles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Validation\ValidationException;

class CreateStorageFolder
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    /** Makes a folder inside another; a name that a folder or file already has there is refused. Returns the path. */
    public function handle(StorageType $type, FilesystemAdapter $disk, string $folder, string $name, ?User $actor): string
    {
        $target = $folder === '' ? $name : "{$folder}/{$name}";

        if ($disk->exists($target)) {
            throw ValidationException::withMessages(['name' => 'Nama sudah dipakai di folder ini.']);
        }

        $disk->makeDirectory($target);

        $this->recordAudit->handle(
            AuditEvent::Created,
            'Folder dibuat',
            causer: $actor,
            properties: ['storage' => $type->value, 'path' => $target],
            module: 'Penyimpanan',
        );

        return $target;
    }
}
