<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->configureRateLimiting();
    }

    /**
     * Configure the rate limiters for the application.
     */
    protected function configureRateLimiting(): void
    {
        // Login rate limiting - 5 attempts per minute per email
        RateLimiter::for('login', function (Request $request) {
            return Limit::perMinute(5)->by($request->input('email'));
        });

        // Registration rate limiting - 3 attempts per minute per email
        RateLimiter::for('registration', function (Request $request) {
            return Limit::perMinute(3)->by($request->input('email'));
        });

        // Password reset rate limiting - 3 attempts per hour per email
        RateLimiter::for('password-reset', function (Request $request) {
            return Limit::perHour(3)->by($request->input('email'));
        });

        // Email verification rate limiting - 5 attempts per hour per user
        RateLimiter::for('email-verification', function (Request $request) {
            return $request->user()
                ? Limit::perHour(5)->by($request->user()->id)
                : Limit::perHour(5)->by($request->ip());
        });
    }
}
