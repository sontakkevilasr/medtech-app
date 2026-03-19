<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PremiumDoctor
{
    /**
     * Blocks access to premium features (timelines, Excel export,
     * WhatsApp reminders) if doctor has no active premium subscription.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        $isPremium = $user->doctorProfile?->is_premium
            || $user->activeSubscription()->exists();

        if (! $isPremium) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'This feature requires a Premium subscription.',
                    'upgrade_url' => route('doctor.subscription.plans'),
                ], 403);
            }

            return redirect()->route('doctor.subscription.plans')
                ->with('info', 'Upgrade to Premium to unlock this feature.');
        }

        return $next($request);
    }
}
