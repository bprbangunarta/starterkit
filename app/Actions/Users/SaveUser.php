<?php

namespace App\Actions\Users;

use App\Actions\Audit\RecordAudit;
use App\Actions\Auth\SetPassword;
use App\Enums\AuditEvent;
use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SaveUser
{
    public function __construct(
        private readonly RecordAudit $recordAudit,
        private readonly SetPassword $setPassword,
        private readonly DefaultUserPassword $defaultPassword,
    ) {}

    /**
     * Creates the user, or changes an existing one, and sets the roles. Changes of the account itself are audited by the model; a change of
     * roles is a separate event, because the roles live in another table.
     *
     * @param  array{name: string, username: string, email: string, office_id?: int|string|null}  $data
     * @param  list<string>  $roles
     */
    public function handle(?User $user, array $data, array $roles, ?User $actor, ?string $reason = null): User
    {
        return DB::transaction(function () use ($user, $data, $roles, $actor, $reason): User {
            $user ??= new User;
            $before = $user->exists ? $this->roleNames($user) : [];
            $after = collect($roles)->unique()->sort()->values()->all();

            $this->keepOneSuperAdmin($before, $after);

            $user->fill(Arr::only($data, ['name', 'username', 'email']));
            $user->office_id = isset($data['office_id']) ? (int) $data['office_id'] : null;

            // A new account starts with the default password and must change it at the first sign-in.
            $user->exists ? $user->save() : $this->setPassword->assignTemporary($user, $this->defaultPassword->handle());
            $user->syncRoles($after);

            if ($before !== $after) {
                $this->recordAudit->handle(
                    AuditEvent::RoleChanged,
                    'Jabatan pengguna diubah',
                    causer: $actor,
                    subject: $user,
                    properties: ['reason' => $reason],
                    changes: ['old' => ['roles' => $before], 'attributes' => ['roles' => $after]],
                );
            }

            return $user;
        });
    }

    /** @return list<string> */
    private function roleNames(User $user): array
    {
        return $user->roles()->pluck('name')->sort()->values()->all();
    }

    /**
     * @param  list<string>  $before
     * @param  list<string>  $after
     */
    private function keepOneSuperAdmin(array $before, array $after): void
    {
        $name = RoleName::SuperAdmin->value;

        if (! in_array($name, $before, true) || in_array($name, $after, true)) {
            return;
        }

        // The rows are locked, so two admins removing each other's role at the same time cannot leave nobody.
        if (User::role($name)->lockForUpdate()->count() <= 1) {
            throw ValidationException::withMessages(['roles' => 'Harus tersisa minimal satu Super Admin.']);
        }
    }
}
