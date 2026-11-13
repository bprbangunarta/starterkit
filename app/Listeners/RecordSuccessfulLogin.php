<?php

namespace App\Listeners;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use Illuminate\Auth\Events\Login;
use Illuminate\Database\Eloquent\Model;

class RecordSuccessfulLogin
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function handle(Login $event): void
    {
        $user = $event->user instanceof Model ? $event->user : null;

        $this->recordAudit->handle(AuditEvent::Login, 'Masuk', causer: $user, subject: $user);
    }
}
