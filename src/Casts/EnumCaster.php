<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

class EnumCaster implements CastsAttributes
{


    public function get(?Model $model, string $key, mixed $value, array $attributes): CastableEnum|null
    {
        return $this->deserializeEnum($value);
    }

    protected function deserializeEnum($value)
    {
        if (empty($value)) {
            return null;
        }
        $value = json_decode($value, true);

        $enumClass = $value['class']??null;

        if (!class_exists($enumClass)) {
            throw new \InvalidArgumentException("Enum class $enumClass does not exist.");
        }

        return $enumClass::tryFrom($value['value']);
    }

    public function set(?Model $model, string $key, mixed $value, array $attributes)
    {
        if (empty($value)) {
            return null;
        }

        return json_encode($this->serializeEnum($value));
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