<?php

namespace App\Actions\Audit;

use App\Enums\AuditEvent;
use App\Enums\AuditExportStatus;
use App\Models\AuditActivity;
use App\Models\AuditExport;
use App\Models\User;
use App\Notifications\AuditExportReady;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;

class WriteAuditExport
{
    private const HEADER = [
        'ID', 'Waktu (UTC)', 'Pengguna', 'ID pengguna', 'Modul', 'Kejadian', 'Tipe data', 'ID data', 'Hasil',
        'Keterangan', 'Alamat IP', 'Metode', 'Path', 'Peramban', 'ID permintaan',
    ];

    public function __construct(private readonly RequestAuditExport $request) {}

    /** Safe to run twice: an export that is already ready is left as it is, so the person gets one file and one notice. */
    public function handle(AuditExport $export): void
    {
        $export->refresh();

        if ($export->status === AuditExportStatus::Ready) {
            return;
        }

        $path = "audit-exports/{$export->id}.csv";
        $file = tmpfile();

        try {
            fwrite($file, "\xEF\xBB\xBF");

            foreach ($this->lines($export) as $line) {
                fputcsv($file, $line, escape: '');
            }

            rewind($file);
            Storage::writeStream($path, $file);
        } finally {
            fclose($file);
        }

        $export->update(['status' => AuditExportStatus::Ready, 'path' => $path, 'ready_at' => now()]);
        $export->user->notify(new AuditExportReady($export));
    }

    /**
     * The header, then the rows newest first, one chunk at a time so the file never sits in memory.
     *
     * @return LazyCollection<int, list<string>>
     */
    private function lines(AuditExport $export): LazyCollection
    {
        $entries = AuditActivity::query()
            ->with(['causer' => fn (MorphTo $causer) => $causer->withTrashed()])
            ->filtered($export->filters)
            ->where('id', '<=', $export->last_id)
            ->lazyByIdDesc(1000)
            ->take($this->request->maxRows())
            ->map(fn (AuditActivity $entry): array => $this->row($entry));

        return LazyCollection::make(function () use ($entries) {
            yield self::HEADER;

            yield from $entries;
        });
    }

    /** @return list<string> */
    private function row(AuditActivity $entry): array
    {
        $causer = $entry->causer instanceof User ? $entry->causer : null;
        $anonymousName = $entry->ip_address === null ? 'Sistem' : 'Tamu';

        return array_map($this->neutralized(...), [
            (string) $entry->id,
            $entry->created_at->utc()->format('Y-m-d H:i:s'),
            $causer === null ? $anonymousName : $causer->name,
            $causer === null ? '' : (string) $causer->id,
            (string) $entry->log_name,
            $this->eventLabel($entry),
            $entry->subject_type === null ? '' : class_basename($entry->subject_type),
            (string) $entry->subject_id,
            $entry->result->label(),
            $entry->description,
            (string) $entry->ip_address,
            (string) $entry->method,
            (string) $entry->path,
            (string) $entry->user_agent,
            (string) $entry->request_id,
        ]);
    }

    private function eventLabel(AuditActivity $entry): string
    {
        $event = AuditEvent::tryFrom((string) $entry->event);

        return $event === null ? (string) $entry->event : $event->label();
    }

    /** A cell that starts with = + - @ is read as a formula by spreadsheets, so it gets a leading quote. */
    private function neutralized(string $cell): string
    {
        return preg_match('/^[=+\-@\t\r]/', $cell) === 1 ? "'".$cell : $cell;
    }
}
