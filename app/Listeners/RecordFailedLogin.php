<?php

namespace App\Listeners;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use Illuminate\Auth\Events\Failed;
use Illuminate\Database\Eloquent\Model;

class RecordFailedLogin
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function handle(Failed $event): void
    {
        $account = $event->user instanceof Model ? $event->user : null;

        $this->recordAudit->handle(AuditEvent::LoginFailed, 'Gagal masuk', subject: $account, result: AuditResult::Failed);
    }
}
