<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;

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
        date_default_timezone_set(env('APP_TIMEZONE', 'UTC'));

        // Set Carbon's default timezone
        Carbon::setTestNow(Carbon::now()->timezone(env('APP_TIMEZONE', 'UTC')));
    

        CarbonImmutable::setTestNow(CarbonImmutable::now()->timezone(env('APP_TIMEZONE', 'UTC')));
    }
}
