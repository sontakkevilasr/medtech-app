<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Usage in routes:  ->middleware('role:doctor')
     *                   ->middleware('role:doctor,admin')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return redirect()->route('auth.login')
                ->with('error', 'Please log in to continue.');
        }

        if (! in_array($request->user()->role, $roles)) {
            // Redirect to their own dashboard instead of a 403
            return redirect()->route($request->user()->role . '.dashboard')
                ->with('error', 'You do not have permission to access that page.');
        }

        return $next($request);
    }
}
