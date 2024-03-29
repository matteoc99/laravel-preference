<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

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

    public function toArrayOnly(array $keys): array
    {
        return Arr::only($this->attributesToArray(), $keys);
    }

}