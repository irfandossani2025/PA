<?php

namespace App\Providers;

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
    }
}
