<?php

namespace App\Providers;

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
        // Set timezone for the application
        date_default_timezone_set('Asia/Kolkata');

        // Set timezone for database connections
        config(['app.timezone' => 'Asia/Kolkata']);
    }
}
