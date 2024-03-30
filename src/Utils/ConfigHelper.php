<?php

namespace Matteoc99\LaravelPreference\Utils;

use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;

class ConfigHelper
{

    public static function getDbConnection(): null|string
    {
        return Config::get('user_preference.db.connection');
    }

    public static function areRoutesEnabled(): bool
    {
        return Config::get('user_preference.routes.enabled', false);
    }

    public static function getRoutePrefix(): string
    {
        $prefix = Config::get('user_preference.routes.name_prefix', 'preferences');

        if (!Str::endsWith($prefix, '.')) {
            $prefix .= '.';
        }

        return $prefix;
    }

    public static function getGroup(string $groupName): string|null
    {
        return Config::get("user_preference.routes.groups.{$groupName}");
    }

    public static function getScope(string $scopeName): string|null
    {
        return Config::get("user_preference.routes.scopes.{$scopeName}");
    }

    public static function getGroups(): array
    {
        return array_keys(Config::get("user_preference.routes.groups"));
    }

    public static function getScopes(): array
    {
        return array_keys(Config::get("user_preference.routes.scopes"));
    }

    public static function getAllMiddlewares(): array
    {
        return Config::get('user_preference.routes.middlewares', []);
    }

    public static function getGlobalMiddlewares(): array
    {
        return array_values(array_filter(self::getAllMiddlewares(), 'is_int', ARRAY_FILTER_USE_KEY));
    }

    public static function getScopedMiddlewares($scopeName): array
    {
        return array_values(array_filter(self::getAllMiddlewares(), function ($key) use ($scopeName) {
            return $key == $scopeName;
        }, ARRAY_FILTER_USE_KEY)) ?? [];
    }

}