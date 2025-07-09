<?php

namespace App\Http\Middleware;

use App\Models\AuditLog;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AuditMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        // Process the request
        $response = $next($request);

        $endTime = microtime(true);
        $duration = round(($endTime - $startTime) * 1000, 2); // Duration in milliseconds

        // Log the request if it meets certain criteria
        $this->logRequest($request, $response, $duration);

        return $response;
    }

    /**
     * Log the request based on criteria.
     */
    private function logRequest(Request $request, Response $response, float $duration): void
    {
        // Skip logging for certain routes
        if ($this->shouldSkipLogging($request)) {
            return;
        }

        $riskLevel = $this->determineRiskLevel($request, $response);

        // Only log if it's medium or high risk, or if there's an error
        if ($riskLevel === 'low' && $response->getStatusCode() < 400) {
            return;
        }

        $user = Auth::user();
        $action = $this->determineAction($request, $response);

        AuditLog::create([
            'user_id' => $user ? $user->id : null,
            'action' => $action,
            'auditable_type' => null,
            'auditable_id' => null,
            'old_values' => [],
            'new_values' => [
                'method' => $request->method(),
                'path' => $request->path(),
                'status_code' => $response->getStatusCode(),
                'duration_ms' => $duration,
                'user_agent' => $request->userAgent(),
            ],
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'risk_level' => $riskLevel,
            'description' => $this->generateDescription($request, $response, $user),
        ]);
    }

    /**
     * Determine if we should skip logging this request.
     */
    private function shouldSkipLogging(Request $request): bool
    {
        $skipPaths = [
            'api/v1/health',
            '_debugbar',
            'telescope',
            'horizon',
            'favicon.ico',
            'api/v1/auth/tokens', // Don't log token requests to avoid sensitive data
        ];

        $path = $request->path();

        foreach ($skipPaths as $skipPath) {
            if (str_starts_with($path, $skipPath)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine the risk level of the request.
     */
    private function determineRiskLevel(Request $request, Response $response): string
    {
        // High risk conditions
        if ($response->getStatusCode() >= 500) {
            return 'high';
        }

        if ($response->getStatusCode() === 403 || $response->getStatusCode() === 401) {
            return 'high';
        }

        $highRiskPaths = [
            'admin/',
            'api/v1/job-posts',
            'api/v1/resumes',
            'api/v1/user',
        ];

        $path = $request->path();
        foreach ($highRiskPaths as $highRiskPath) {
            if (str_starts_with($path, $highRiskPath)) {
                return 'medium';
            }
        }

        // Check for suspicious patterns
        if ($this->hasSuspiciousPatterns($request)) {
            return 'high';
        }

        return 'low';
    }

    /**
     * Determine the action from the request.
     */
    private function determineAction(Request $request, Response $response): string
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();

        if ($statusCode >= 400) {
            return 'request_failed';
        }

        if (str_contains($path, 'login')) {
            return 'api_login_attempt';
        }

        if (str_contains($path, 'job-posts')) {
            return match ($method) {
                'GET' => 'view_job_posts',
                'POST' => 'create_job_post',
                'PUT', 'PATCH' => 'update_job_post',
                'DELETE' => 'delete_job_post',
                default => 'api_request'
            };
        }

        return 'api_request';
    }

    /**
     * Check for suspicious patterns in the request.
     */
    private function hasSuspiciousPatterns(Request $request): bool
    {
        $suspiciousPatterns = [
            'script', 'javascript:', 'onload=', 'onerror=', 'onclick=',
            'union select', 'drop table', 'insert into', 'delete from',
            '../', '..\\', '/etc/passwd', '/proc/self',
            'base64_decode', 'eval(', 'system(', 'exec(',
        ];

        $content = strtolower($request->getContent());
        $queryString = strtolower($request->getQueryString() ?? '');

        foreach ($suspiciousPatterns as $pattern) {
            if (str_contains($content, $pattern) || str_contains($queryString, $pattern)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Generate a description for the log entry.
     */
    private function generateDescription(Request $request, Response $response, $user): string
    {
        $method = $request->method();
        $path = $request->path();
        $statusCode = $response->getStatusCode();
        $userName = $user ? $user->name : 'Anonymous';

        if ($statusCode >= 400) {
            return "Failed {$method} request to {$path} by {$userName} (Status: {$statusCode})";
        }

        return "{$method} request to {$path} by {$userName} (Status: {$statusCode})";
    }
}
