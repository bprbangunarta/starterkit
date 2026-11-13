<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnforceSessionLifetime
{
    private const STARTED_AT = 'auth_started_at';

    /** Laravel only expires idle sessions, so the maximum age of a signed-in session is enforced here. */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user()) {
            return $next($request);
        }

        $session = $request->session();
        $startedAt = $session->get(self::STARTED_AT);

        if ($startedAt === null) {
            $session->put(self::STARTED_AT, now()->getTimestamp());

            return $next($request);
        }

        if (now()->getTimestamp() - $startedAt <= (int) config('session.absolute_lifetime') * 60) {
            return $next($request);
        }

        Auth::guard()->logout();
        $session->invalidate();
        $session->regenerateToken();

        return redirect()->route('login')->with('message', 'Sesi Anda berakhir. Silakan masuk lagi.');
    }
}
