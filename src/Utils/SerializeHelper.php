<?php

namespace Matteoc99\LaravelPreference\Utils;

use BackedEnum;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Models\Preference;
use RuntimeException;

class SerializeHelper
{
    public static function enumToString(PreferenceGroup|string $value): string
    {
        if (!$value instanceof PreferenceGroup) return $value;

        return $value->value;
    }

    /**
     * splits a preference enum into name and group as string
     *
     * @param PreferenceGroup|string $name
     * @param string|null            $group
     *
     * @return void
     */
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

    /**
     * inverse of the above, reconstructs the original enum
     *
     * @param Preference $preference
     *
     * @return PreferenceGroup
     */
    public static function reversePreferenceToEnum(Preference $preference): PreferenceGroup
    {

        $enumClass = $preference->group;
        $enumValue = $preference->name;

        if (!class_exists($enumClass)) {
            throw new InvalidArgumentException("Enum class $enumClass does not exist.");
        }

        if (!in_array(BackedEnum::class, class_implements($enumClass))) {
            throw new InvalidArgumentException("Enum class $enumClass must be a backed enum.");
        }

        return $enumClass::tryFrom($enumValue);

    }
}