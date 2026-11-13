<?php

namespace App\Actions\StorageFiles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class UploadStorageFile
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    /** Writes the file into the folder as a stream, under its own name; a file with that name is never overwritten. Returns the path. */
    public function handle(StorageType $type, FilesystemAdapter $disk, string $folder, UploadedFile $file, ?User $actor): string
    {
        $name = $this->safeName($file->getClientOriginalName());
        $target = $folder === '' ? $name : "{$folder}/{$name}";

        if ($disk->exists($target)) {
            throw ValidationException::withMessages(['file' => 'Berkas dengan nama itu sudah ada di folder ini.']);
        }

        $stream = fopen($file->getRealPath(), 'rb');

        try {
            $disk->writeStream($target, $stream);
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }

        $this->recordAudit->handle(
            AuditEvent::Created,
            'Berkas diunggah',
            causer: $actor,
            properties: ['storage' => $type->value, 'path' => $target, 'size' => $file->getSize()],
            module: 'Penyimpanan',
        );

        return $target;
    }

    /**
     * The name the person's computer gave the file, without any folder part or control character. A longer name is refused rather than cut,
     * since a cut could remove the extension that was checked.
     */
    private function safeName(string $original): string
    {
        $name = Str::of(basename(str_replace('\\', '/', $original)))->replaceMatches('/[\x00-\x1F\x7F]/', '')->trim()->toString();

        if (mb_strlen($name) > 200) {
            throw ValidationException::withMessages(['file' => 'Nama berkas paling banyak 200 karakter.']);
        }

        return $name === '' ? 'berkas' : $name;
    }
}
