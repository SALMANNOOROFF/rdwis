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
        $this->app->singleton(\App\Services\FileStorageService::class, function () {
            return new \App\Services\FileStorageService();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFour();

        if (str_starts_with((string) config('app.url'), 'https://') || request()->server('HTTP_X_FORWARDED_PROTO') === 'https') {
            \Illuminate\Support\Facades\URL::forceScheme('https');
        }

        Auth::provider('cen_accounts', function ($app, array $config) {
            return new CenAccountUserProvider($app['hash'], $config['model']);
        });

        // Blade directive: @fileUrl($relativePath)
        \Illuminate\Support\Facades\Blade::directive('fileUrl', function ($expression) {
            return "<?php echo \App\Facades\FileStorage::url($expression); ?>";
        });

        // View Composer for server-side instant sidebar badges
        view()->composer('welcome', function ($view) {
            $badges = \App\Services\SidebarBadgeService::getBadgesForUser();
            $view->with('sidebarBadges', $badges);
        });
    }
}

