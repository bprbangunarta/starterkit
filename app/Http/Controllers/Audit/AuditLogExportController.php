<?php

namespace App\Http\Controllers\Audit;

use App\Actions\Audit\RequestAuditExport;
use App\Enums\AuditExportStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\AuditLogIndexRequest;
use App\Models\AuditExport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AuditLogExportController extends Controller
{
    /** Queues the export and returns at once; the person is told in the notification bell when the file is ready. */
    public function store(AuditLogIndexRequest $request, RequestAuditExport $export): RedirectResponse
    {
        if (($refusal = $export->refusal($request->user())) !== null) {
            return back()->withErrors(['export' => $refusal]);
        }

        $filters = $request->filters();
        $rows = $export->count($filters);

        if ($rows > $export->maxRows()) {
            return back()->withErrors(['export' => sprintf(
                'Ekspor dibatasi %s baris, sedangkan hasil filter ini %s baris. Persempit filter lalu coba lagi.',
                number_format($export->maxRows(), 0, ',', '.'),
                number_format($rows, 0, ',', '.'),
            )]);
        }

        $export->handle($filters, $request->user(), $rows);

        return back();
    }

    /** Only the person who asked can download, and only while the file is ready and not expired. */
    public function show(Request $request, string $export): StreamedResponse
    {
        $record = AuditExport::query()
            ->whereBelongsTo($request->user())
            ->where('status', AuditExportStatus::Ready)
            ->where('expires_at', '>', now())
            ->findOrFail($export);

        abort_unless($record->path !== null && Storage::exists($record->path), 404);

        return Storage::download($record->path, 'audit-logs-'.$record->created_at->format('Ymd-His').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
