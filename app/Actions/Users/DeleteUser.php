<?php

namespace App\Actions\Users;

use App\Enums\RoleName;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DeleteUser
{
    /** The account is soft deleted: it can no longer sign in, and its history stays in the audit log. */
    public function handle(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => 'Anda tidak dapat menghapus akun Anda sendiri.']);
        }

        DB::transaction(function () use ($user): void {
            // The Super Admin rows are locked, so two admins deleting each other at the same time cannot leave nobody.
            $admins = User::role(RoleName::SuperAdmin->value)->lockForUpdate()->pluck('users.id');

            if ($admins->contains($user->id) && $admins->count() <= 1) {
                throw ValidationException::withMessages(['user' => 'Super Admin terakhir tidak dapat dihapus.']);
            }

            $user->delete();
        });
    }
}
