<?php

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * TLS terminates at the Cloudflare tunnel, so the container is reached over
     * plain HTTP. Left null (Laravel's stub) this middleware is registered but
     * trusts nothing, which behaves exactly like not having it: every generated
     * URL keeps the http:// scheme, the page loads over https, and the browser
     * blocks the result as mixed content -- while curl still reports 200.
     *
     * Safe because the container is only reachable through the tunnel and the
     * internal Docker network.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies = '*';

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers =
        Request::HEADER_X_FORWARDED_FOR |
        Request::HEADER_X_FORWARDED_HOST |
        Request::HEADER_X_FORWARDED_PORT |
        Request::HEADER_X_FORWARDED_PROTO |
        Request::HEADER_X_FORWARDED_AWS_ELB;
}
