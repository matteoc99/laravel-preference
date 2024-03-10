<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Support\Facades\App;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

class Enum implements CastsAttributes
{


    public function get($model, string $key, mixed $value, array $attributes): CastableEnum
    {
        return $this->deserializeEnum($attributes);
    }

    protected function deserializeEnum($value)
    {
        if (empty($value)) {
            return null;
        }

        $enumClass = $value['class'];

        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class $enumClass does not exist.");
        }

        /**@var CastableEnum $enum * */
        $enum = App::make($enumClass);

        return $enum->tryFrom($value['value']);
    }

    public function set($model, string $key, mixed $value, array $attributes): array
    {
        return $this->serializeEnum($value);
    }

    protected function serializeEnum($enum): array
    {
        if (!$enum instanceof CastableEnum) {
            throw new \InvalidArgumentException("Invalid value for Castable attribute.");
        }

        return [
            'class' => get_class($enum),
            'value' => $enum->value,
        ];
    }
}