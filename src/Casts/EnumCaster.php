<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;

class EnumCaster implements CastsAttributes
{


    public function get(?Model $model, string $key, mixed $value, array $attributes): CastableEnum|null
    {
        return SerializeHelper::deserializeEnum($value);
    }

    public function set(?Model $model, string $key, mixed $value, array $attributes): ?string
    {
        if (empty($value)) {
            return null;
        }

        return $this->serializeEnum($value);
    }

    protected function serializeEnum($enum): string
    {
        if (!$enum instanceof CastableEnum) {
            throw new InvalidArgumentException("Invalid value for Castable attribute.");
        }

        return SerializeHelper::serializeEnum($enum);
    }
}