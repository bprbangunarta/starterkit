<?php

namespace App\Notifications;

use App\Models\AuditExport;
use Illuminate\Notifications\Notification;

class AuditExportReady extends Notification
{
    public function __construct(private readonly AuditExport $export) {}

    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array{title: string, body: string, level: string, url: string} */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Ekspor audit siap diunduh'),
            'body' => __(':rows baris. Berkas tersedia :hours jam.', [
                'rows' => number_format($this->export->rows, 0, ',', '.'),
                'hours' => config('activitylog.export_keep_hours'),
            ]),
            'level' => 'success',
            'url' => route('audit-logs.exports.show', $this->export, absolute: false),
        ];
    }
}
