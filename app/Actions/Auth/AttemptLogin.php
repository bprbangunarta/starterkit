<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AttemptLogin
{
    /** Signs in by email when the login contains `@`, otherwise by username. */
    public function handle(string $login, string $password): bool
    {
        $login = Str::lower(trim($login));
        $field = str_contains($login, '@') ? 'email' : 'username';

        // The guard only hashes when the account exists, so hash anyway to answer as slowly for a missing one.
        if (! User::query()->where($field, $login)->exists()) {
            Hash::make($password);
        }

        return Auth::attempt([$field => $login, 'password' => $password]);
    }
}
