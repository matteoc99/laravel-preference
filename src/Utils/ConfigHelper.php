<?php

namespace Matteoc99\LaravelPreference\Utils;

use Illuminate\Support\Facades\Config;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;

class ConfigHelper
{
    public static function getDbConnection(): ?string
    {
        return Config::get('user_preference.db.connection');
    }

    public static function isXssCleanEnabled(): bool
    {
        $enabled = Config::get('user_preference.xss_cleaning', true);

        return $enabled && class_exists('GrahamCampbell\\SecurityCore\\Security');
    }

    public static function getDbTableName(string $class, string $default = ''): string
    {
        $config = match ($class) {
            Preference::class => 'preferences_table_name',
            UserPreference::class => 'user_preferences_table_name',
            default => throw new InvalidArgumentException("Unsupported class: $class"),
        };

        return Config::get("user_preference.db.$config", $default);
    }

    public static function areRoutesEnabled(): bool
    {
        return Config::get('user_preference.routes.enabled', false);
    }

    public static function getRoutePrefix(bool $dotted = true): string
    {
        $prefix = Config::get('user_preference.routes.name_prefix', 'preferences');

        return rtrim($prefix, '.').($dotted ? '.' : '');
    }

    public static function getGroup(string $groupName): ?string
    {
        return Config::get("user_preference.routes.groups.$groupName");
    }

    public static function getScope(string $scopeName): ?string
    {
        return Config::get("user_preference.routes.scopes.$scopeName");
    }

    public static function getGroups(): array
    {
        return array_keys(Config::get('user_preference.routes.groups'));
    }

    public static function getScopes(): array
    {
        return array_keys(Config::get('user_preference.routes.scopes'));
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

    public static function getScopeGroupedMiddlewares($scopeName, $groupName): array
    {
        $name = $scopeName.'.'.$groupName;

        return array_values(array_filter(self::getAllMiddlewares(), function ($key) use ($name) {
            return $key == $name;
        }, ARRAY_FILTER_USE_KEY)) ?? [];
    }
}
