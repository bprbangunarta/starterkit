<?php

namespace App\Actions\Roles;

use App\Enums\RoleName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncBaseRoles
{
    public function __construct(private readonly PermissionRegistrar $registrar) {}

    /**
     * Safe to run again: only roles that do not exist yet are created.
     *
     * @return list<string> The roles this run created.
     */
    public function handle(): array
    {
        $guard = (string) config('auth.defaults.guard');
        $existing = Role::query()->where('guard_name', $guard)->pluck('name');
        $created = [];

        DB::transaction(function () use ($guard, $existing, &$created): void {
            foreach (RoleName::cases() as $name) {
                Role::findOrCreate($name, $guard);

                if (! $existing->contains($name->value)) {
                    $created[] = $name->value;
                }
            }
        });

        if ($created !== []) {
            Log::info('Role dasar dibuat', ['roles' => $created]);
        }

        // Seeders mute model events, so saving a role does not clear this cache by itself.
        $this->registrar->forgetCachedPermissions();

        return $created;
    }
}
