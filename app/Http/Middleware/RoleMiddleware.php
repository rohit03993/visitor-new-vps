<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $user = auth()->user();
        
        // Handle comma-separated roles (e.g., 'admin,staff')
        $allowedRoles = [];
        foreach ($roles as $roleString) {
            $allowedRoles = array_merge($allowedRoles, array_map('trim', explode(',', $roleString)));
        }
        
        // Remove duplicates
        $allowedRoles = array_unique($allowedRoles);
        
        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Unauthorized access.');
        }

        return $next($request);
    }
}
