<?php

namespace App\Actions\Users;

use App\Actions\Audit\RecordAudit;
use App\Actions\Auth\SetPassword;
use App\Enums\AuditEvent;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ResetUserPassword
{
    public function __construct(
        private readonly SetPassword $setPassword,
        private readonly DefaultUserPassword $defaultPassword,
        private readonly RecordAudit $recordAudit,
    ) {}

    /** Gives the account the default password, makes the person choose a new one, and signs the account out everywhere. */
    public function handle(User $user, User $actor): void
    {
        if ($user->is($actor)) {
            throw ValidationException::withMessages(['user' => 'Gunakan halaman Profil untuk mengganti kata sandi Anda sendiri.']);
        }

        DB::transaction(function () use ($user, $actor): void {
            $this->setPassword->assignTemporary($user, $this->defaultPassword->handle());

            DB::table('sessions')->where('user_id', $user->id)->delete();

            $this->recordAudit->handle(AuditEvent::PasswordReset, 'Kata sandi diatur ulang', causer: $actor, subject: $user);
        });
    }
}
