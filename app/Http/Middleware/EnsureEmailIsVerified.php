<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is not authenticated or email is verified, proceed
        if (!$user || $user->hasVerifiedEmail()) {
            return $next($request);
        }

        // For API requests, return JSON error
        if ($request->expectsJson() || $request->is('api/*')) {
            return response()->json([
                'message' => 'Your email address is not verified.',
                'errors' => [
                    'email' => ['Please verify your email address to continue.'],
                ],
            ], 403);
        }

        // For web requests, redirect to verification notice
        return redirect()->route('verify-email');
    }
}

