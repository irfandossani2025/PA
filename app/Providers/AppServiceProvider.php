<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
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
        // The production host uses MariaDB 10.1, whose indexed utf8mb4 values
        // are limited to 767 bytes. Keep indexed strings within that limit.
        Schema::defaultStringLength(191);

        RateLimiter::for('mac-agent', function (Request $request): Limit {
            $device = $request->attributes->get('mac_device');

            return Limit::perMinute(30)->by($device?->id ?? $request->ip());
        });

        RateLimiter::for('login', function (Request $request): Limit {
            return Limit::perMinute(5)->by(strtolower((string) $request->input('email')).'|'.$request->ip());
        });
    }
}
