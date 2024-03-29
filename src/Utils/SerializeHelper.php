<?php

namespace Matteoc99\LaravelPreference\Utils;

use BackedEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use RuntimeException;

class SerializeHelper
{

    public static function serializeEnum($enum): string|null
    {
        if (empty($enum)) return null;

        if (!$enum instanceof BackedEnum) {
            throw new \InvalidArgumentException("Invalid value,Backed enum required for serializing.");
        }

        return json_encode([
            'class' => get_class($enum),
            'value' => $enum->value,
        ]);
    }

    public static function deserializeEnum($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = json_decode($value, true);

        $enumClass = $value['class'] ?? null;

        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class $enumClass does not exist.");
        }

        if (!in_array(BackedEnum::class, class_implements($enumClass))) {
            throw new \InvalidArgumentException("Enum class $enumClass must be a backed enum.");
        }

        return $enumClass::tryFrom($value['value']);
    }

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