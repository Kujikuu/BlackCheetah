<?php

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function () {
            // Configure rate limiters
            RateLimiter::for('login', function (Request $request) {
                return Limit::perMinute(5)->by($request->input('email').'|'.$request->ip());
            });

            RateLimiter::for('password-reset', function (Request $request) {
                return Limit::perHour(3)->by($request->ip());
            });

            RateLimiter::for('registration', function (Request $request) {
                // More relaxed for local development
                return app()->environment('local')
                    ? Limit::perHour(100)->by($request->ip())  // 100/hour in local
                    : Limit::perHour(3)->by($request->ip());   // 3/hour in production
            });

            RateLimiter::for('email-verification', function (Request $request) {
                return Limit::perHour(6)->by($request->user()?->id ?: $request->ip());
            });
        },
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'role' => \App\Http\Middleware\RoleMiddleware::class,
            'onboarding' => \App\Http\Middleware\CheckOnboardingStatus::class,
            'verified' => \App\Http\Middleware\EnsureEmailIsVerified::class,
        ]);

        // Override default authenticate middleware for API
        $middleware->replace(
            \Illuminate\Auth\Middleware\Authenticate::class,
            \App\Http\Middleware\Authenticate::class
        );

        // Add CORS middleware to API routes
        $middleware->api(prepend: [
            \Illuminate\Http\Middleware\HandleCors::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();
