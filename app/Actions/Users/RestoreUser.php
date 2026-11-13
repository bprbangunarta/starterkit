<?php

namespace App\Actions\Users;

use App\Models\User;
use Illuminate\Validation\ValidationException;

class RestoreUser
{
    /** The account can sign in again with its old password and roles; the audit trail records the restore. */
    public function handle(User $user): void
    {
        if (! $user->trashed()) {
            throw ValidationException::withMessages(['user' => 'Pengguna ini tidak sedang dihapus.']);
        }

        $user->restore();
    }
}
