<?php

namespace App\Actions\Audit;

use App\Enums\AuditEvent;
use App\Enums\AuditExportStatus;
use App\Jobs\GenerateAuditExport;
use App\Models\AuditActivity;
use App\Models\AuditExport;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class RequestAuditExport
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function maxRows(): int
    {
        return (int) config('activitylog.export_max_rows');
    }

    /** Why a new export is refused while the person, or everybody together, already holds the most that are waiting or ready; null when it may go on. */
    public function refusal(User $actor): ?string
    {
        $active = AuditExport::query()
            ->where('expires_at', '>', now())
            ->whereIn('status', [AuditExportStatus::Pending, AuditExportStatus::Ready]);

        if ((clone $active)->count() >= (int) config('activitylog.export_max_active')) {
            return 'Terlalu banyak ekspor sedang disimpan. Coba lagi beberapa saat lagi.';
        }

        $own = (int) config('activitylog.export_max_active_per_user');

        if ((clone $active)->where('user_id', $actor->id)->count() >= $own) {
            return "Anda sudah punya {$own} ekspor yang menunggu atau siap diunduh. Unduh salah satunya atau tunggu hingga kedaluwarsa, lalu coba lagi.";
        }

        return null;
    }

    /** @param  array<string, mixed>  $filters */
    public function count(array $filters): int
    {
        return AuditActivity::query()->filtered($filters)->count();
    }

    /**
     * Saves the request and queues the file. The file holds the entries up to `last_id`; the audit entry names the export in its
     * properties (the subject column is numeric) and never keeps the typed search text.
     *
     * @param  array<string, mixed>  $filters
     */
    public function handle(array $filters, User $actor, int $rows): AuditExport
    {
        return DB::transaction(function () use ($filters, $actor, $rows): AuditExport {
            $export = AuditExport::query()->create([
                'user_id' => $actor->id,
                'status' => AuditExportStatus::Pending,
                'filters' => array_intersect_key($filters, array_flip(['search', 'module', 'result', 'from', 'to'])),
                'last_id' => (int) AuditActivity::query()->max('id'),
                'rows' => $rows,
                'expires_at' => now()->addHours((int) config('activitylog.export_keep_hours')),
            ]);

            $this->recordAudit->handle(
                AuditEvent::Exported,
                'Meminta ekspor catatan audit',
                causer: $actor,
                properties: [
                    'export_id' => $export->id,
                    'rows' => $rows,
                    'module' => $filters['module'] ?? null,
                    'result' => $filters['result'] ?? null,
                    'from' => $filters['from'] ?? null,
                    'to' => $filters['to'] ?? null,
                    'search_used' => ($filters['search'] ?? null) !== null,
                ],
            );

            GenerateAuditExport::dispatch($export)->afterCommit();

            return $export;
        });
    }
}
