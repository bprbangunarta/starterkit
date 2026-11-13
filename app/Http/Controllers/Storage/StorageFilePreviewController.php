<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Audit\RecordAudit;
use App\Actions\Storage\RunOnStorage;
use App\Actions\StorageFiles\ResolvePreviewType;
use App\Enums\AuditEvent;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\StorageItemRequest;
use Illuminate\Filesystem\FilesystemAdapter;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class StorageFilePreviewController extends Controller
{
    /** Streams a file of a safe kind to be shown in the page, and records who looked at it. Anything else is only for download. */
    public function show(StorageItemRequest $request, StorageType $type, RunOnStorage $run, ResolvePreviewType $resolve, RecordAudit $recordAudit): StreamedResponse
    {
        $path = $request->path();
        $preview = $resolve->handle($path) ?? throw new HttpException(415, 'Berkas ini tidak bisa dilihat di layar, unduh saja.');

        $response = $run->handle($type, function (FilesystemAdapter $disk) use ($path, $preview): StreamedResponse {
            if (! $disk->fileExists($path)) {
                throw new NotFoundHttpException('Berkas tidak ditemukan.');
            }

            $size = $disk->size($path);

            if ($size > (int) config('storage_connections.preview_max_kb') * 1024) {
                throw new HttpException(413, 'Berkas terlalu besar untuk dilihat di layar, unduh saja.');
            }

            return response()->stream(function () use ($disk, $path): void {
                $stream = $disk->readStream($path);

                try {
                    fpassthru($stream);
                } finally {
                    fclose($stream);
                }
            }, 200, [
                'Content-Type' => $preview['mime'],
                'Content-Length' => $size,
                'Content-Disposition' => HeaderUtils::makeDisposition('inline', basename($path), 'berkas'),
                'Content-Security-Policy' => $preview['kind']->contentSecurityPolicy(),
                'X-Content-Type-Options' => 'nosniff',
                'Cache-Control' => 'private, no-store',
            ]);
        });

        $recordAudit->handle(
            AuditEvent::FilePreviewed,
            'Berkas dilihat',
            causer: $request->user(),
            properties: ['storage' => $type->value, 'path' => $path],
        );

        return $response;
    }
}
