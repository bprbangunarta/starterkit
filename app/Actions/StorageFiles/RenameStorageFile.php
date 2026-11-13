<?php

namespace App\Actions\StorageFiles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RenameStorageFile
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    /** Gives a file another name in the same folder. Only files are renamed: on S3 a folder has no name of its own to change. Returns the new path. */
    public function handle(StorageType $type, FilesystemAdapter $disk, string $path, string $name, ?User $actor): string
    {
        if (! $disk->fileExists($path)) {
            throw new NotFoundHttpException('Berkas tidak ditemukan.');
        }

        $folder = str_contains($path, '/') ? Str::beforeLast($path, '/') : '';
        $target = $folder === '' ? $name : "{$folder}/{$name}";

        if ($target === $path) {
            return $path;
        }

        if ($disk->exists($target)) {
            throw ValidationException::withMessages(['name' => 'Nama sudah dipakai di folder ini.']);
        }

        $disk->move($path, $target);

        $this->recordAudit->handle(
            AuditEvent::Updated,
            'Nama berkas diubah',
            causer: $actor,
            properties: ['storage' => $type->value],
            changes: ['old' => ['path' => $path], 'attributes' => ['path' => $target]],
            module: 'Penyimpanan',
        );

        return $target;
    }
}
