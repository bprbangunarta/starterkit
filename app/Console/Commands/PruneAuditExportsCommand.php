<?php

namespace App\Console\Commands;

use App\Actions\Audit\PruneAuditExports;
use Illuminate\Console\Command;
use Illuminate\Console\ConfirmableTrait;

class PruneAuditExportsCommand extends Command
{
    use ConfirmableTrait;

    protected $signature = 'audit:prune-exports {--force : Run without asking, as the scheduler does}';

    protected $description = 'Delete the audit exports past their expiry, with their files';

    public function handle(PruneAuditExports $prune): int
    {
        if (! $this->confirmToProceed()) {
            return self::FAILURE;
        }

        $this->info("Dihapus: {$prune->handle()} ekspor yang sudah kedaluwarsa.");

        return self::SUCCESS;
    }
}
