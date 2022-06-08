<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class HardenHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $response->header('X-Frame-Options', 'SAMEORIGIN');
        $response->header('X-Content-Type-Options', 'nosniff');
        $response->header('Content-Security-Policy', "script-src 'self' ");
        $response->header('Referrer-Policy', 'strict-origin-when-cross-origin');

        return $response;
    }
}
