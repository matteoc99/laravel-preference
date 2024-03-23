<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

class ValueCaster implements CastsAttributes
{

    public function __construct(protected ?CastableEnum $caster = null) { }

    public function get(?Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) return null;

        $caster = $this->getCaster($model);

        if ($caster) {
            return $caster->castFromString($value);
        }

        //default do nothing
        return $value;

    }

    public function set(?Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) return null;

        $caster = $this->getCaster($model);

        if ($caster) {
            return $caster->castToString($value);
        }

        //default do nothing
        return $value;
    }

    private function getCaster(?Model $model): CastableEnum|null
    {
        $caster = $this->caster ?? $model?->cast ?? $model?->preference?->cast ?? null;

        return $caster instanceof CastableEnum ? $caster : null;
    }
}