<?php

namespace App\Listeners;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use Illuminate\Auth\Events\Logout;
use Illuminate\Database\Eloquent\Model;

class RecordLogout
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function handle(Logout $event): void
    {
        $user = $event->user instanceof Model ? $event->user : null;

        $this->recordAudit->handle(AuditEvent::Logout, 'Keluar', causer: $user, subject: $user);
    }
}
