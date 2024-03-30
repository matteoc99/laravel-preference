<?php

use Illuminate\Support\Facades\Route;
use Matteoc99\LaravelPreference\Controllers\PreferenceController;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;


$prefix = ConfigHelper::getRoutePrefix();
$scopes = ConfigHelper::getScopes();
$groups = ConfigHelper::getGroups();

Route::group(['middleware' => ConfigHelper::getGlobalMiddlewares(), 'prefix' => $prefix], function () use ($groups, $scopes, $prefix) {

    foreach ($scopes as $scope) {
        Route::group(['middleware' => ConfigHelper::getScopedMiddlewares($scope), 'prefix' => $scope], function () use ($scope, $prefix, $groups) {
            foreach ($groups as $group) {
                $name = sprintf("%s%s.%s", $prefix, $scope, $group);
                Route::get("{scope_id}/$group", [PreferenceController::class, 'index'])
                    ->name($name . ".index");
                Route::get("{scope_id}/$group/{preference}", [PreferenceController::class, 'get'])
                    ->name($name . ".get");
                Route::match(['PUT', 'PATCH'], "{scope_id}/$group/{preference}", [PreferenceController::class, 'update'])
                    ->name($name . ".update");
                Route::delete("{scope_id}/$group/{preference}", [PreferenceController::class, 'destroy'])
                    ->name($name . ".destroy");
            }
        });
    }
});
