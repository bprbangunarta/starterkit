<?php

namespace App\Actions\StorageFiles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Models\User;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DeleteStorageItem
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    /** Deletes a file, or a folder that is empty: a folder with things in it is never emptied by one click. */
    public function handle(StorageType $type, FilesystemAdapter $disk, string $path, ?User $actor): void
    {
        $isFile = $disk->fileExists($path);

        if (! $isFile && ! $disk->directoryExists($path)) {
            throw new NotFoundHttpException('Item tidak ditemukan.');
        }

        if ($isFile) {
            $disk->delete($path);
        } else {
            $this->deleteEmptyFolder($disk, $path);
        }

        $this->recordAudit->handle(
            AuditEvent::Deleted,
            $isFile ? 'Berkas dihapus' : 'Folder dihapus',
            causer: $actor,
            properties: ['storage' => $type->value],
            changes: ['old' => ['path' => $path]],
            module: 'Penyimpanan',
        );
    }

    private function deleteEmptyFolder(FilesystemAdapter $disk, string $path): void
    {
        foreach ($disk->getDriver()->listContents($path, false) as $inside) {
            throw ValidationException::withMessages(['path' => 'Folder tidak kosong. Hapus atau pindahkan isinya lebih dulu.']);
        }

        $disk->deleteDirectory($path);
    }
}
