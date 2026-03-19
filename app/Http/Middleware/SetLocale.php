<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Sets the application locale to the authenticated user's preferred language.
     * Supports: en (English), hi (Hindi), mr (Marathi)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            App::setLocale($user->preferred_language ?? 'en');
        } elseif (session()->has('locale')) {
            App::setLocale(session('locale'));
        }

        return $next($request);
    }
}
