<?php

namespace App\Rules;

use App\Models\User;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/** The new password may not equal the current one or the last few before it (`auth.password_history`). */
class NotRecentlyUsedPassword implements ValidationRule
{
    public function __construct(private readonly User $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $limit = (int) config('auth.password_history');

        $hashes = DB::table('password_histories')->where('user_id', $this->user->id)->orderByDesc('id')->limit($limit)->pluck('password')
            ->push($this->user->password)
            ->unique();

        if ($hashes->contains(fn (string $hash): bool => Hash::check((string) $value, $hash))) {
            $fail("Kata sandi tidak boleh sama dengan {$limit} kata sandi terakhir.");
        }
    }
}
