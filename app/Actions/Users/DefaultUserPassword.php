<?php

namespace App\Actions\Users;

use RuntimeException;

class DefaultUserPassword
{
    /** The password a new or reset account starts with, from `DEFAULT_USER_PASSWORD` in .env. */
    public function handle(): string
    {
        $password = config('auth.default_user_password');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('DEFAULT_USER_PASSWORD belum diatur di .env, jadi kata sandi awal tidak bisa dibuat.');
        }

        return $password;
    }
}
