<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ApiAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated via Sanctum
        if (! Auth::guard('sanctum')->check()) {
            return response()->json([
                'message' => 'Unauthenticated.',
                'error' => 'API_AUTHENTICATION_REQUIRED',
            ], 401);
        }

        // Additional security checks
        $user = Auth::guard('sanctum')->user();

        // Check if user account is active
        if (! $user || $user->deleted_at) {
            return response()->json([
                'message' => 'Account is inactive or has been deleted.',
                'error' => 'ACCOUNT_INACTIVE',
            ], 403);
        }

        // Check if email is verified for sensitive operations
        if (! $user->hasVerifiedEmail() && $this->requiresVerifiedEmail($request)) {
            return response()->json([
                'message' => 'Email verification required for this action.',
                'error' => 'EMAIL_VERIFICATION_REQUIRED',
            ], 403);
        }

        // Rate limiting for API calls
        $this->checkRateLimit($request, $user);

        return $next($request);
    }

    /**
     * Check if the request requires email verification.
     */
    private function requiresVerifiedEmail(Request $request): bool
    {
        $sensitiveRoutes = [
            'api/job-posts',
            'api/resumes',
            'api/applications',
            'api/user/profile',
        ];

        $path = $request->path();
        foreach ($sensitiveRoutes as $route) {
            if (str_starts_with($path, $route)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Basic rate limiting check.
     */
    private function checkRateLimit(Request $request, $user): void
    {
        $key = 'api_rate_limit:'.$user->id;
        $maxAttempts = 100; // requests per hour
        $decayMinutes = 60;

        if (cache()->has($key)) {
            $attempts = cache()->get($key);
            if ($attempts >= $maxAttempts) {
                abort(429, 'Too many API requests. Please try again later.');
            }
            cache()->put($key, $attempts + 1, now()->addMinutes($decayMinutes));
        } else {
            cache()->put($key, 1, now()->addMinutes($decayMinutes));
        }
    }
}
