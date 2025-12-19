<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL;

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


    //  public function boot(): void
    //  {
    //      if (request()->header('host') && str_contains(request()->header('host'), 'ngrok-free.app')) {
    //          URL::forceScheme('https');
    //      }
    //  }

    // public function boot(): void
    // {
    //     if (config('app.env') == 'test') {
    //         URL::forceScheme('https'); // Force all URLs to use HTTPS
    //     }
    // }

    public function boot(): void
    {
        // URL::forceScheme('https'); // Force all URLs to use HTTPS

        if (config('app.env') == 'test') {
            URL::forceScheme('https'); // Force all URLs to use HTTPS
        }
    }

}

// Note: If you change the APP_ENV make sure
// to run "php artisan config:clear, php
// artisan cache:clear, php artisan config:cache"
