<?php

namespace App\Actions\Settings;

use App\Actions\Storage\RunOnStorage;
use App\Enums\SettingKey;
use App\Services\ProjectSettings;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Throwable;

class RemoveBrandImage
{
    public function __construct(
        private readonly ProjectSettings $settings,
        private readonly UpdateProjectSettings $update,
        private readonly RunOnStorage $run,
    ) {}

    /** Takes the image back to the bundled one, and removes the uploaded file. */
    public function handle(SettingKey $kind): void
    {
        $previous = $this->settings->image($kind);

        $this->update->handle([$kind->value => null]);

        if ($previous === null) {
            return;
        }

        try {
            $this->run->handle($previous['storage'], fn (FilesystemAdapter $disk): bool => $disk->delete($previous['path']));
        } catch (Throwable $error) {
            Log::warning('Gambar merek yang dilepas tidak bisa dihapus', ['storage' => $previous['storage']->value, 'path' => $previous['path'], 'error' => $error::class]);
        }
    }
}
