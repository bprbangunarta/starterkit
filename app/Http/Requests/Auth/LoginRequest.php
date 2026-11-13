<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    private const MAX_ATTEMPTS = 5;

    private const FIRST_LOCKOUT_SECONDS = 60;

    private const MAX_LOCKOUT_DOUBLINGS = 4;

    private const LOCKOUT_MEMORY_SECONDS = 86400;

    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<string>> */
    public function rules(): array
    {
        return [
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string', 'max:128'],
        ];
    }

    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->lockKey(), 1)) {
            return;
        }

        throw ValidationException::withMessages([
            'login' => trans('auth.throttle', ['seconds' => RateLimiter::availableIn($this->lockKey())]),
        ]);
    }

    /** The fifth failure locks this login and IP; every later lockout lasts twice as long, up to 16 minutes. */
    public function recordFailure(): void
    {
        RateLimiter::hit($this->attemptsKey());

        if (! RateLimiter::tooManyAttempts($this->attemptsKey(), self::MAX_ATTEMPTS)) {
            return;
        }

        $lockouts = RateLimiter::increment($this->lockoutsKey(), self::LOCKOUT_MEMORY_SECONDS);

        RateLimiter::hit($this->lockKey(), self::FIRST_LOCKOUT_SECONDS * 2 ** min($lockouts - 1, self::MAX_LOCKOUT_DOUBLINGS));
        RateLimiter::clear($this->attemptsKey());

        event(new Lockout($this));
    }

    public function clearFailures(): void
    {
        RateLimiter::clear($this->attemptsKey());
        RateLimiter::clear($this->lockoutsKey());
    }

    /** Hashed, so what was typed in the login field never becomes a cache key. */
    private function identity(): string
    {
        return hash('sha256', $this->string('login')->trim()->lower()->transliterate()->toString().'|'.$this->ip());
    }

    private function attemptsKey(): string
    {
        return 'login-attempts:'.$this->identity();
    }

    private function lockKey(): string
    {
        return 'login-lock:'.$this->identity();
    }

    private function lockoutsKey(): string
    {
        return 'login-lockouts:'.$this->identity();
    }
}
