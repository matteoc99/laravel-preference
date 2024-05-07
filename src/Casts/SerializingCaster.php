<?php

namespace Matteoc99\LaravelPreference\Casts;

use Illuminate\Database\Eloquent\Model;

class SerializingCaster
{
    public function get(?Model $model, string $key, mixed $value, array $attributes)
    {

        return empty($value) ? $value : unserialize($value);
    }

    public function set(?Model $model, string $key, mixed $value, array $attributes)
    {
        return empty($value) ? $value : serialize($value);
    }
}
