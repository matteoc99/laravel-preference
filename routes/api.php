<?php

use Illuminate\Support\Facades\Route;
use Matteoc99\LaravelPreference\PreferenceController;


$prefix = config('user_preference.routes.name_prefix', 'preferences');
$scopes = array_keys(config('user_preference.routes.scopes', ['user']));
$groups = array_keys(config('user_preference.routes.groups', ['general']));

$middlewares = config('user_preference.routes.middlewares', []);

$globalMiddlewares = array_values(array_filter($middlewares, 'is_int', ARRAY_FILTER_USE_KEY));

Route::group(['middleware' => config('user_preference.routes.middlewares', []), 'prefix' => $prefix], function () use ($middlewares, $groups, $scopes, $prefix) {

    foreach ($scopes as $scope) {
        $localMiddlewares = array_values(array_filter($middlewares, function ($key) use ($scope) {
            return $key == $scope;
        }, ARRAY_FILTER_USE_KEY)) ?? [];

        Route::group(['middleware' => $localMiddlewares, 'prefix' => $scope], function () use ($scope, $prefix, $groups) {
            foreach ($groups as $group) {
                $name = sprintf("%s.%s.%s", $prefix, $scope, $group);
                Route::group(['prefix' => $group], function () use ($name) {
                    Route::get('/{group}', [PreferenceController::class, 'index'])->name($name . ".index");
                    Route::get('/{group}/{preference}', [PreferenceController::class, 'get'])->name($name . ".get");
                    Route::match(['PUT', 'PATCH'], '/{group}/{preference}', [PreferenceController::class, 'update'])->name($name . ".update");
                    Route::delete('/{service}/{route}', [PreferenceController::class, 'destroy'])->name($name . ".destroy");
                });
            }
        });
    }
});
