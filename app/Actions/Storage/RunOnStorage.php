<?php

namespace App\Actions\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Closure;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Throwable;

class RunOnStorage
{
    public function __construct(
        private readonly ResolveStorageConfig $resolve,
        private readonly AssertHostAllowed $hosts,
        private readonly BuildStorageDisk $disks,
        private readonly StorageErrorMessage $errors,
    ) {}

    /**
     * Runs an operation on the connection in force for a kind of storage. A connection that is missing, points inside the network, or
     * fails comes back as a validation error under `storage`; errors the operation raises itself pass through.
     *
     * @template T
     *
     * @param  Closure(FilesystemAdapter): T  $operation
     * @return T
     *
     * @throws ValidationException
     */
    public function handle(StorageType $type, Closure $operation): mixed
    {
        $resolved = $this->resolve->handle($type, StorageSetting::query()->where('type', $type->value)->first());

        if ($resolved === null) {
            $this->refuse('Konfigurasi penyimpanan belum lengkap.');
        }

        $host = $type->hostOf($resolved['disk']);
        $refusal = $resolved['source'] === 'screen' && $host !== '' ? $this->hosts->refusal($host) : null;

        if ($refusal !== null) {
            $this->refuse($refusal);
        }

        try {
            return $operation($this->disks->forFiles($type, $resolved['disk']));
        } catch (ValidationException|HttpExceptionInterface $error) {
            throw $error;
        } catch (Throwable $error) {
            // The person sees a cleaned message; the operator needs to know that the storage is failing, and of what kind.
            Log::warning('Penyimpanan tidak dapat dipakai', ['storage' => $type->value, 'error' => $error::class]);
            $this->refuse($this->errors->handle('Penyimpanan tidak dapat dipakai: ', $error, $resolved['disk']));
        }
    }

    /** @throws ValidationException */
    private function refuse(string $message): never
    {
        throw ValidationException::withMessages(['storage' => $message]);
    }
}
