<?php

namespace Matteoc99\LaravelPreference;

use Illuminate\Support\ServiceProvider;
use Matteoc99\LaravelPreference\Console\Commands\PreferenceMigrate;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;

class PreferenceServiceProvider extends ServiceProvider
{
    public function register()
    {

    }

    public function boot(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

        $this->publishes([
            __DIR__.'/../config/user_preference.php' => config_path('user_preference.php'),
        ], 'laravel-preference-config');

        if (ConfigHelper::areRoutesEnabled()) {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        }

        if ($this->app->runningInConsole()) {
            $this->commands([
                PreferenceMigrate::class,
            ]);
        }
    }
}
