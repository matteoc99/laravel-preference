<?php

namespace Matteoc99\LaravelPreference\Models;

use Carbon\Language;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;

class BaseModel extends Model
{

    public function __construct(array $attributes = [])
    {
        $configConnection = config('user_preference.db.connection');
        if (empty($this->connection) && !empty($configConnection)) {
            $this->setConnection($configConnection);
        }

        parent::__construct($attributes);
    }

    public function toArrayOnly(array $keys)
    {
        return Arr::only($this->attributesToArray(), $keys);
    }

}