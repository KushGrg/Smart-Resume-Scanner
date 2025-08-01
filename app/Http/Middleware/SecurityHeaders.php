<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');

        // Content Security Policy
        $csp = [
            "default-src 'self'",
            "script-src 'self' 'unsafe-inline' 'unsafe-eval' cdn.jsdelivr.net unpkg.com".(app()->environment('local') ? ' 127.0.0.1:5173 localhost:5173' : ''),
            "style-src 'self' 'unsafe-inline' fonts.googleapis.com cdn.jsdelivr.net".(app()->environment('local') ? ' 127.0.0.1:5173 localhost:5173' : ''),
            "img-src 'self' data: http: blob:",
            "font-src 'self' fonts.gstatic.com",
            "connect-src 'self' data:".(app()->environment('local') ? ' ws://127.0.0.1:5173 ws://localhost:5173 127.0.0.1:5173 localhost:5173' : ''),
            "media-src 'self'",
            "object-src 'none'",
            "child-src 'self'",
            "worker-src 'none'",
            "form-action 'self'",
            "base-uri 'self'",
            "manifest-src 'self'",
        ];

        $response->headers->set('Content-Security-Policy', implode('; ', $csp));

        // HSTS for HTTPS
        if ($request->isSecure()) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains; preload');
        }

        return $response;
    }
}
