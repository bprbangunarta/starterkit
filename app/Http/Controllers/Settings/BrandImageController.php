<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Storage\RunOnStorage;
use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use App\Services\ProjectSettings;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class BrandImageController extends Controller
{
    /**
     * Streams the uploaded logo or favicon. It is public because the sign-in page shows it; the address carries a version, so browsers may
     * keep it for good. A storage that cannot be reached answers 404 and the page falls back to its bundled image.
     */
    public function show(SettingKey $kind, ProjectSettings $settings, RunOnStorage $run): StreamedResponse
    {
        $image = $settings->image($kind) ?? throw new NotFoundHttpException;
        $type = config('project.image_types')[strtolower(pathinfo($image['path'], PATHINFO_EXTENSION))] ?? throw new NotFoundHttpException;

        try {
            return $run->handle($image['storage'], function (FilesystemAdapter $disk) use ($image, $type): StreamedResponse {
                if (! $disk->fileExists($image['path'])) {
                    throw new NotFoundHttpException;
                }

                return response()->stream(function () use ($disk, $image): void {
                    $stream = $disk->readStream($image['path']);

                    try {
                        fpassthru($stream);
                    } finally {
                        fclose($stream);
                    }
                }, 200, [
                    'Content-Type' => $type,
                    'Content-Security-Policy' => "default-src 'none'; sandbox",
                    'X-Content-Type-Options' => 'nosniff',
                    'Cache-Control' => 'public, max-age=31536000, immutable',
                ]);
            });
        } catch (ValidationException) {
            throw new NotFoundHttpException;
        }
    }
}
