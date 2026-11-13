<?php

namespace App\Actions\Audit;

use App\Models\AuditExport;
use Illuminate\Support\Facades\Storage;

class PruneAuditExports
{
    /** Deletes the exports past their expiry together with their files, and returns how many there were. */
    public function handle(): int
    {
        $count = 0;

        AuditExport::query()->where('expires_at', '<', now())->chunkById(100, function ($exports) use (&$count): void {
            foreach ($exports as $export) {
                if ($export->path !== null) {
                    Storage::delete($export->path);
                }

                $export->delete();
                $count++;
            }
        });

        return $count;
    }
}
