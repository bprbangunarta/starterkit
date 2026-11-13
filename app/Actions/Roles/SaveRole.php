<?php

namespace App\Actions\Roles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class SaveRole
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    /** Creates a role, or renames one; the base roles keep their names because the application refers to them. */
    public function handle(?Role $role, string $name, ?User $actor): Role
    {
        if ($role !== null && RoleName::tryFrom($role->name) !== null && $role->name !== $name) {
            throw ValidationException::withMessages(['name' => 'Nama jabatan dasar tidak dapat diubah.']);
        }

        return DB::transaction(function () use ($role, $name, $actor): Role {
            $old = $role?->name;
            $role ??= new Role(['guard_name' => config('auth.defaults.guard')]);
            $role->name = $name;
            $role->save();

            $this->recordAudit->handle(
                $old === null ? AuditEvent::Created : AuditEvent::Updated,
                $old === null ? 'Jabatan dibuat' : 'Jabatan diubah',
                causer: $actor,
                subject: $role,
                changes: ['old' => $old === null ? [] : ['name' => $old], 'attributes' => ['name' => $name]],
                module: 'Jabatan',
            );

            return $role;
        });
    }
}
