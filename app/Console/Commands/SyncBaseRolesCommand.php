<?php

namespace App\Console\Commands;

use App\Actions\Roles\SyncBaseRoles;
use Illuminate\Console\Command;

class SyncBaseRolesCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'roles:sync-base';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create the base roles (Super Admin, Guest) that do not exist yet. Safe to run on every deploy.';

    public function handle(SyncBaseRoles $syncBaseRoles): int
    {
        $created = $syncBaseRoles->handle();

        $this->info($created === [] ? 'Semua role dasar sudah ada.' : 'Role dibuat: '.implode(', ', $created).'.');

        return self::SUCCESS;
    }
}
