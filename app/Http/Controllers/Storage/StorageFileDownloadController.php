<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Audit\RecordAudit;
use App\Actions\Storage\RunOnStorage;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\StorageItemRequest;
use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StorageFileDownloadController extends Controller
{
    /** Streams a file to the browser as a download (never shown inline, so a file cannot run as a page), and records who took it. */
    public function show(StorageItemRequest $request, StorageType $type, RunOnStorage $run, RecordAudit $recordAudit): StreamedResponse
    {
        $path = $request->path();

        $response = $run->handle($type, function (FilesystemAdapter $disk) use ($path): StreamedResponse {
            if (! $disk->fileExists($path)) {
                throw new NotFoundHttpException('Berkas tidak ditemukan.');
            }

            return response()->streamDownload(function () use ($disk, $path): void {
                $stream = $disk->readStream($path);

                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            }, basename($path), ['Content-Type' => 'application/octet-stream', 'X-Content-Type-Options' => 'nosniff']);
        });

        $recordAudit->handle(
            AuditEvent::FileDownloaded,
            'Berkas diunduh',
            causer: $request->user(),
            properties: ['storage' => $type->value, 'path' => $path],
        );

        return $response;
    }
}
