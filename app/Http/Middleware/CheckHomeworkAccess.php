<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckHomeworkAccess
{
    /**
     * Handle an incoming request.
     * Check if user (admin or staff) has permission to access homework section
     * Students are not affected as they use different authentication guard
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Students use different guard (student) and are not affected by this middleware
        // They can access their own routes without permission checks
        if (auth()->guard('student')->check()) {
            return $next($request);
        }
        
        $user = auth()->guard('web')->user();
        
        // If no user is authenticated via web guard, deny access
        if (!$user) {
            abort(403, 'Unauthorized access.');
        }
        
        // Admin always has access
        if ($user->isAdmin()) {
            return $next($request);
        }
        
        // Staff needs explicit permission
        if ($user->isStaff() && !$user->canAccessHomework()) {
            abort(403, 'You do not have permission to access the homework section. Please contact your administrator.');
        }
        
        return $next($request);
    }
}
