<?php

use Illuminate\Support\Facades\Route;
use Matteoc99\LaravelPreference\Http\Controllers\PreferenceController;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;

$scopes = ConfigHelper::getScopes();
$groups = ConfigHelper::getGroups();

Route::group(['middleware' => ConfigHelper::getGlobalMiddlewares(), 'prefix' => ConfigHelper::getRoutePrefix(false)], function () use ($groups, $scopes) {

    foreach ($scopes as $scope) {
        Route::group(['middleware' => ConfigHelper::getScopedMiddlewares($scope), 'prefix' => $scope], function () use ($scope, $groups) {
            foreach ($groups as $group) {
                $name = sprintf("%s%s.%s", ConfigHelper::getRoutePrefix(), $scope, $group);
                Route::group(['middleware' => ConfigHelper::getScopeGroupedMiddlewares($scope, $group)], function () use ($name, $group) {
                    Route::get("{scope_id}/$group", [PreferenceController::class, 'index'])
                        ->name($name . ".index");
                    Route::get("{scope_id}/$group/{preference}", [PreferenceController::class, 'get'])
                        ->name($name . ".get");
                    Route::match(['PUT', 'PATCH'], "{scope_id}/$group/{preference}", [PreferenceController::class, 'update'])
                        ->name($name . ".update");
                    Route::delete("{scope_id}/$group/{preference}", [PreferenceController::class, 'delete'])
                        ->name($name . ".delete");
                });
            }
        });
    }
});
