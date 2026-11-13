<?php

namespace App\Actions\Storage;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Enums\StorageType;
use App\Models\StorageSetting;
use App\Models\User;
use Illuminate\Support\Str;
use Throwable;

class TestStorageConnection
{
    public function __construct(
        private readonly ResolveStorageConfig $resolve,
        private readonly AssertHostAllowed $hosts,
        private readonly BuildStorageDisk $disks,
        private readonly StorageErrorMessage $errors,
        private readonly RecordAudit $recordAudit,
    ) {}

    /**
     * Connects with the connection in force and lists the top folder. The outcome is kept beside the settings (without an audit entry of
     * its own, since the test is its own audit event) and written to the audit trail, never with a secret.
     *
     * @return array{ok: bool, message: string}
     */
    public function handle(StorageType $type, ?User $actor): array
    {
        $row = StorageSetting::query()->where('type', $type->value)->first() ?? new StorageSetting(['type' => $type]);
        $resolved = $this->resolve->handle($type, $row);
        $outcome = $resolved === null ? ['ok' => false, 'message' => 'Konfigurasi belum lengkap.'] : $this->connect($type, $resolved);

        $row->forceFill(['tested_at' => now(), 'test_ok' => $outcome['ok'], 'test_message' => Str::limit($outcome['message'], 240, '')])->saveQuietly();

        $this->recordAudit->handle(
            AuditEvent::StorageTested,
            'Uji koneksi penyimpanan',
            causer: $actor,
            properties: ['type' => $type->value, 'source' => $resolved['source'] ?? null],
            result: $outcome['ok'] ? AuditResult::Succeeded : AuditResult::Failed,
        );

        return $outcome;
    }

    /**
     * @param  array{source: 'screen'|'env', disk: array<string, mixed>}  $resolved
     * @return array{ok: bool, message: string}
     */
    private function connect(StorageType $type, array $resolved): array
    {
        // Hosts typed on the screen are checked; the ones in the server environment are the operator's own.
        $refusal = $resolved['source'] === 'screen' ? $this->hostRefusal($type, $resolved['disk']) : null;

        if ($refusal !== null) {
            return ['ok' => false, 'message' => $refusal];
        }

        try {
            $this->disks->forTest($type, $resolved['disk'])->directories('');
        } catch (Throwable $error) {
            return ['ok' => false, 'message' => $this->errors->handle('Gagal terhubung: ', $error, $resolved['disk'])];
        }

        return ['ok' => true, 'message' => 'Terhubung.'];
    }

    /** @param  array<string, mixed>  $disk */
    private function hostRefusal(StorageType $type, array $disk): ?string
    {
        $host = $type->hostOf($disk);

        return $host === '' ? null : $this->hosts->refusal($host);
    }
}
