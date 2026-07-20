<?php

namespace App\Providers;

use App\Auth\CenAccountUserProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
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
        Paginator::useBootstrapFour();

        Auth::provider('cen_accounts', function ($app, array $config) {
            return new CenAccountUserProvider($app['hash'], $config['model']);
        });
    }
}
