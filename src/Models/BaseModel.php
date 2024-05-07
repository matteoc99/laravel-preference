<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Matteoc99\LaravelPreference\Utils\ConfigHelper;

class BaseModel extends Model
{
    public function __construct(array $attributes = [])
    {
        $configConnection = ConfigHelper::getDbConnection();
        if (empty($this->connection) && ! empty($configConnection)) {
            $this->setConnection($configConnection);
        }

        parent::__construct($attributes);
    }

    public function toArrayOnly(array $keys): array
    {
        return Arr::only($this->attributesToArray(), $keys);
    }

    public function getTable()
    {
        return ConfigHelper::getDbTableName(get_class($this), parent::getTable());
    }
}
