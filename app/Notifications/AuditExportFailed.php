<?php

namespace App\Notifications;

use Illuminate\Notifications\Notification;

class AuditExportFailed extends Notification
{
    /** @return list<string> */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /** @return array{title: string, body: string, level: string, url: null} */
    public function toArray(object $notifiable): array
    {
        return [
            'title' => __('Ekspor audit gagal'),
            'body' => __('Berkas tidak dapat dibuat. Coba lagi, atau hubungi tim TI bila berulang.'),
            'level' => 'warning',
            'url' => null,
        ];
    }
}
