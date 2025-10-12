<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip check if user is not authenticated or not a franchisee
        if (! $user || $user->role !== 'franchisee') {
            return $next($request);
        }

        // Skip check for onboarding-related routes
        $onboardingRoutes = [
            'api/v1/onboarding/status',
            'api/v1/onboarding/complete',
            'api/v1/auth/logout',
        ];

        if (in_array($request->path(), $onboardingRoutes)) {
            return $next($request);
        }

        // Check if profile is incomplete
        if (! $user->profile_completed) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Profile completion required',
                    'requires_onboarding' => true,
                    'redirect_to' => '/onboarding',
                ], 403);
            }

            // For web requests, redirect to onboarding page
            return redirect('/onboarding');
        }

        return $next($request);
    }
}
