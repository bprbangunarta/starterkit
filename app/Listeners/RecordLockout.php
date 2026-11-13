<?php

namespace App\Listeners;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use Illuminate\Auth\Events\Lockout;

class RecordLockout
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function handle(Lockout $event): void
    {
        $this->recordAudit->handle(AuditEvent::Lockout, 'Terkunci karena terlalu banyak percobaan masuk', result: AuditResult::Refused);
    }
}
