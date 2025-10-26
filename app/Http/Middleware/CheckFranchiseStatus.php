<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckFranchiseStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        // Skip check if user is not authenticated or not a franchisor
        if (! $user || $user->role !== 'franchisor') {
            return $next($request);
        }

        // Skip check for franchise registration and auth routes
        $allowedRoutes = [
            'api/v1/onboarding/franchise-status',
            'api/v1/auth/logout',
            'api/v1/auth/me',
            'api/v1/account/settings',
            'api/v1/account/password',
        ];

        // Allow franchise registration routes
        if (str_contains($request->path(), 'franchise-registration') || 
            str_contains($request->path(), 'franchises') && $request->method() === 'POST') {
            return $next($request);
        }

        if (in_array($request->path(), $allowedRoutes)) {
            return $next($request);
        }

        // Check if franchisor has a franchise
        $franchise = $user->franchise()->first();

        if (!$franchise) {
            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => 'Franchise registration required',
                    'requires_franchise_registration' => true,
                    'redirect_to' => '/franchisor/franchise-registration',
                ], 403);
            }

            // For web requests, redirect to franchise registration page
            return redirect('/franchisor/franchise-registration');
        }

        return $next($request);
    }
}

