<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class AssignRequestId
{
    /** The ID is always generated here, never taken from a client header. The IP, method, path (never the query string), and user agent stay out of log lines (hidden context). */
    public function handle(Request $request, Closure $next): Response
    {
        $requestId = (string) Str::uuid();

        Context::add('request_id', $requestId);
        Context::addHidden('ip_address', $request->ip());
        Context::addHidden('request_method', $request->method());
        Context::addHidden('request_path', Str::limit('/'.ltrim($request->path(), '/'), 255, ''));
        Context::addHidden('user_agent', Str::limit((string) $request->userAgent(), 255, ''));

        $response = $next($request);
        $response->headers->set('X-Request-Id', $requestId);

        return $response;
    }
}
