<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class PerformanceMonitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage();

        $response = $next($request);

        $endTime = microtime(true);
        $endMemory = memory_get_usage();

        $executionTime = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = $endMemory - $startMemory;

        // Log performance metrics for slow requests (> 500ms) or high memory usage (> 10MB)
        if ($executionTime > 500 || $memoryUsed > 10485760) {
            Log::warning('Performance Alert', [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'execution_time_ms' => round($executionTime, 2),
                'memory_used_bytes' => $memoryUsed,
                'memory_used_mb' => round($memoryUsed / 1048576, 2),
                'user_agent' => $request->userAgent(),
                'ip' => $request->ip()
            ]);
        }

        // Add performance headers for monitoring
        $response->headers->set('X-Execution-Time', round($executionTime, 2) . 'ms');
        $response->headers->set('X-Memory-Used', round($memoryUsed / 1048576, 2) . 'MB');

        return $response;
    }
}
