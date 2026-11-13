<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * A sensitive action needs a password confirmation from the last `auth.password_timeout` seconds. Without one the answer is 423, which the
 * screen turns into the confirmation modal; there is no page to redirect to.
 */
class EnsurePasswordIsConfirmed
{
    public function handle(Request $request, Closure $next): Response
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        if (now()->timestamp - $confirmedAt > (int) config('auth.password_timeout')) {
            return response()->json(['message' => 'Konfirmasi kata sandi diperlukan.', 'code' => 'reauth_required'], 423);
        }

        return $next($request);
    }
}
