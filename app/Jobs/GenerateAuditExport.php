<?php

namespace App\Jobs;

use App\Actions\Audit\WriteAuditExport;
use App\Enums\AuditExportStatus;
use App\Models\AuditExport;
use App\Notifications\AuditExportFailed;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Queue\SerializesModels;

/** Writes the file of one audit export. The timeout stays below the queue's `retry_after` (90 s), so two workers never take the same job. */
class GenerateAuditExport implements ShouldBeUnique, ShouldQueue
{
    use Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 80;

    /** @var list<int> */
    public array $backoff = [10, 60];

    public function __construct(public readonly AuditExport $export) {}

    public function uniqueId(): string
    {
        return $this->export->id;
    }

    public function handle(WriteAuditExport $write): void
    {
        $write->handle($this->export);
    }

    /** After the last retry, the person is told instead of waiting for a file that will not come. */
    public function failed(): void
    {
        $this->export->update(['status' => AuditExportStatus::Failed]);
        $this->export->user->notify(new AuditExportFailed);
    }
}
