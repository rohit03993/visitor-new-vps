<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HomeworkAuth
{
    /**
     * Handle an incoming request.
     *
     * This middleware allows access if user is authenticated as either:
     * - Staff/Admin (web guard - VmsUser)
     * - Student (student guard - Visitor)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if authenticated as staff/admin (web guard)
        $isStaff = Auth::guard('web')->check();
        
        // Check if authenticated as student (student guard)
        $isStudent = Auth::guard('student')->check();

        // If neither staff nor student is authenticated, redirect to student login
        if (!$isStaff && !$isStudent) {
            return redirect()->route('homework.login');
        }

        return $next($request);
    }
}

