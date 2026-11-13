<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetSecurityHeaders
{
    /**
     * The same protective headers on every answer of the app, error pages included. It is the outermost middleware, so the errors that other
     * global middleware raises (an untrusted host, maintenance, a body that is too big) get them too. A header a controller already set for its own
     * reason stays: the file streams carry their own policy (a sandbox), and the logo is meant to be kept by browsers. The CSP names only
     * what is safe to state for every page (framing, base address, forms, plugins), since the pages load scripts and styles of their own.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');

        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', "frame-ancestors 'self'; base-uri 'self'; form-action 'self'; object-src 'none'");
        }

        // What a signed-in person is shown can hold personal data, so no cache may keep it. A page for a guest holds none, and an answer
        // that chose to be public (the logo) is left alone.
        if ($request->user() !== null && ! $response->headers->hasCacheControlDirective('public')) {
            $response->headers->addCacheControlDirective('no-store');
        }

        return $response;
    }
}
