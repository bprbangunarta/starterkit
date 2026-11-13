<?php

namespace Database\Seeders;

use App\Actions\Roles\SyncBaseRoles;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /** Reuses the action that production runs on deploy (`roles:sync-base`). */
    public function run(SyncBaseRoles $syncBaseRoles): void
    {
        $syncBaseRoles->handle();
    }
}
