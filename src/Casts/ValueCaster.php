<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

class ValueCaster implements CastsAttributes
{
    public function __construct(protected ?CastableEnum $caster = null)
    {
    }

    public function get(?Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        $caster = $this->getCaster($model, $attributes);

        if ($caster) {
            return $caster->castFromString($value);
        }

        return $value;

    }

    public function set(?Model $model, string $key, mixed $value, array $attributes)
    {
        if (is_null($value)) {
            return null;
        }

        $caster = $this->getCaster($model, $attributes);

        if ($caster) {
            return $caster->castToString($value);
        }

        return $value;
    }

    private function getCaster(?Model $model, array $attributes): ?CastableEnum
    {
        if (array_key_exists('cast', $attributes)) {
            $caster = unserialize($attributes['cast']);
        } elseif (is_null($model)) {
            $caster = $this->caster;
        } else {
            $caster = $model->cast ?? null;
            if (is_null($caster) && $model->isRelation('preference')) {
                if (! $model->relationLoaded('preference')) {
                    $model->load('preference');
                }
                $caster = $model->preference->cast ?? null;
            }
        }

        return $caster instanceof CastableEnum ? $caster : null;
    }
}
