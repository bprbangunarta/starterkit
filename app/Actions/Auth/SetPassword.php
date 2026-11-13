<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\DB;

class SetPassword
{
    /** The person chose this password: it is valid for the full lifetime and goes into the history. */
    public function change(User $user, string $password): void
    {
        $this->store($user, $password, now());
    }

    /**
     * An admin or the system gave this password (a new account, or a reset): it has no change date, so it counts as expired and the
     * person must choose their own at the first sign-in. The user is saved here too, so a new account is written once.
     */
    public function assignTemporary(User $user, string $password): void
    {
        $this->store($user, $password, null);
    }

    private function store(User $user, string $password, mixed $changedAt): void
    {
        DB::transaction(function () use ($user, $password, $changedAt): void {
            $user->forceFill(['password' => $password, 'password_changed_at' => $changedAt])->save();

            DB::table('password_histories')->insert(['user_id' => $user->id, 'password' => $user->password, 'created_at' => now()]);

            $keep = DB::table('password_histories')->where('user_id', $user->id)->orderByDesc('id')->limit((int) config('auth.password_history'))->pluck('id');
            DB::table('password_histories')->where('user_id', $user->id)->whereNotIn('id', $keep)->delete();
        });
    }
}
