<?php

namespace Matteoc99\LaravelPreference;

use Illuminate\Support\ServiceProvider;

class PreferenceServiceProvider extends ServiceProvider
{

    public function register()
    {


    }

    public function boot()
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        $this->publishes([
            __DIR__ . '/../config/user_preference.php' => config_path('user_preference.php'),
        ], 'laravel-preference-config');

        if (config('user_preference.routes.enabled', false)) {
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        }
    }
}