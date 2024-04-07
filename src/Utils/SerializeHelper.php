<?php

namespace Matteoc99\LaravelPreference\Utils;

use BackedEnum;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use RuntimeException;

class SerializeHelper
{
    public static function enumToString(PreferenceGroup|string $value): string
    {
        if (!$value instanceof PreferenceGroup) return $value;

        return $value->value;
    }

    public static function conformNameAndGroup(PreferenceGroup|string &$name, string|null &$group): void
    {
        //auto set group scope for enums
        if (empty($group)) {
            if ($name instanceof PreferenceGroup) {
                $group = $name::class;
            } else {
                throw new RuntimeException('name can not be a string if group is empty ');
            }
        }

        $name = SerializeHelper::enumToString($name);
    }
}