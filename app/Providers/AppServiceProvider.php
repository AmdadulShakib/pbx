<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\File;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        if (file_exists(app_path('Support/helpers.php'))) {
            require_once app_path('Support/helpers.php');
        }

        File::ensureDirectoryExists(storage_path('framework/views'));
        File::ensureDirectoryExists(storage_path('framework/cache'));
        File::ensureDirectoryExists(storage_path('framework/sessions'));
        File::ensureDirectoryExists(storage_path('logs'));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        Gate::before(function ($user, string $ability) {
            if (method_exists($user, 'hasPermission')) {
                return $user->hasPermission($ability) ? true : null;
            }
            return null;
        });
//        $this->registerPolicies();

//        Passport::routes();
    }
}
