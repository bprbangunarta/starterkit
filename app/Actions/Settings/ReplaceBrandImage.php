<?php

namespace App\Actions\Settings;

use App\Actions\Storage\RunOnStorage;
use App\Enums\SettingKey;
use App\Enums\StorageType;
use App\Services\ProjectSettings;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

class ReplaceBrandImage
{
    public function __construct(
        private readonly ProjectSettings $settings,
        private readonly UpdateProjectSettings $update,
        private readonly RunOnStorage $run,
    ) {}

    /**
     * Saves the image to the active storage under a new name, makes it the one in force, and then removes the one it replaces. The new
     * name changes the address, so no browser keeps showing the old image.
     */
    public function handle(SettingKey $kind, UploadedFile $file): void
    {
        $previous = $this->settings->image($kind);
        $storage = $this->settings->activeStorage();
        $name = $kind->value.'-'.Str::lower(Str::random(12)).'.'.strtolower((string) $file->guessExtension());

        $path = $this->run->handle($storage, fn (FilesystemAdapter $disk): string => (string) $disk->putFileAs((string) config('project.images_folder'), $file, $name));

        try {
            $this->update->handle([$kind->value => json_encode(['storage' => $storage->value, 'path' => $path], JSON_THROW_ON_ERROR)]);
        } catch (Throwable $error) {
            // The file was written but never became the one in force, so nothing would ever remove it.
            $this->discard($storage, $path);

            throw $error;
        }

        if ($previous !== null) {
            $this->discard($previous['storage'], $previous['path']);
        }
    }

    /** The replaced image is no longer in use; failing to remove it is logged, not shown, since the new one is already in force. */
    private function discard(StorageType $storage, string $path): void
    {
        try {
            $this->run->handle($storage, fn (FilesystemAdapter $disk): bool => $disk->delete($path));
        } catch (Throwable $error) {
            Log::warning('Gambar merek yang lama tidak bisa dihapus', ['storage' => $storage->value, 'path' => $path, 'error' => $error::class]);
        }
    }
}
