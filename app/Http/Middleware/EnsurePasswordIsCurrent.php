<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/** A person whose password is new, reset, or older than its lifetime can only change it (or sign out) until they have. */
class EnsurePasswordIsCurrent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user !== null && $user->passwordExpired() && ! $request->routeIs('password.expired', 'profile.password.update', 'logout')) {
            return redirect()->route('password.expired');
        }

        return $next($request);
    }
}
