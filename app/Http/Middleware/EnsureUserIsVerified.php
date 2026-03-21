<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsVerified
{
    /**
     * Blocks users who have registered but not yet verified their OTP.
     * Applied globally to all authenticated routes.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user && ! $user->is_verified) {
            // Store intended URL so we can redirect back after verification
            session(['url.intended' => $request->url()]);

            return redirect()->route('auth.otp.verify')
                ->with('info', 'Please verify your mobile number to continue.');
        }

        return $next($request);
    }
}
