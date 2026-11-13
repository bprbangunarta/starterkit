<?php

namespace App\Actions\Roles;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public function __construct(private readonly RecordAudit $recordAudit) {}

    public function handle(Role $role, ?User $actor): void
    {
        if (RoleName::tryFrom($role->name) !== null) {
            throw ValidationException::withMessages(['role' => 'Jabatan dasar tidak dapat dihapus.']);
        }

        DB::transaction(function () use ($role, $actor): void {
            // The row is locked while it is checked, so a role cannot be deleted at the moment it is given to a user.
            Role::query()->whereKey($role->id)->lockForUpdate()->first();

            if ($role->users()->exists()) {
                throw ValidationException::withMessages(['role' => 'Jabatan masih dipakai pengguna. Pindahkan penggunanya lebih dulu.']);
            }

            $name = $role->name;
            $role->delete();

            $this->recordAudit->handle(AuditEvent::Deleted, 'Jabatan dihapus', causer: $actor, subject: $role, changes: ['old' => ['name' => $name]], module: 'Jabatan');
        });
    }
}
