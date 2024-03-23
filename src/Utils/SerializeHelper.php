<?php

namespace Matteoc99\LaravelPreference\Utils;

use BackedEnum;
use UnitEnum;

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

    public static function enumToString(UnitEnum|string $value): string
    {
        if (!$value instanceof UnitEnum) return $value;

        if ($value instanceof \StringBackedEnum) return $value->value;

        return $value->name;
    }

    public static function conformNameAndGroup(UnitEnum|string &$name, string|null &$group): void
    {
        //auto set group scope for enums
        if (empty($group)) {
            if ($name instanceof UnitEnum) {
                $group = $name::class;
            } else {
                $group = "general";
            }
        }else{
            trigger_error('Setting the group manually is deprecated', E_USER_DEPRECATED);
        }


        if(is_string($name)){
            trigger_error('Preference Name as String is going to be deprecated in the next major release', E_USER_DEPRECATED);
        }

        $name = SerializeHelper::enumToString($name);

    }


}