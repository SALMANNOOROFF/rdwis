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

        // Register Dynamic Authorization Gates
        \Illuminate\Support\Facades\Gate::before(function ($user, $ability) {
            if ($user instanceof \App\Models\CenAccount && \App\Services\Auth\UserAccessContext::forUser($user)->isSuperAdmin()) {
                return true;
            }
        });

        foreach (\App\Services\Auth\PermissionRegistry::all() as $permission) {
            \Illuminate\Support\Facades\Gate::define($permission, function ($user) use ($permission) {
                if ($user instanceof \App\Models\CenAccount) {
                    return \App\Services\Auth\RolePermissionMap::hasPermission($user, $permission);
                }
                return false;
            });
        }

        // Register Model Policies
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Project::class, \App\Policies\ProjectPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\Purchase::class, \App\Policies\PurchaseCasePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\HrCtrCase::class, \App\Policies\ContractCasePolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\FinSalOrder::class, \App\Policies\SalaryOrderPolicy::class);
        \Illuminate\Support\Facades\Gate::policy(\App\Models\AudRev::class, \App\Policies\DataRevisionPolicy::class);
    }
}

