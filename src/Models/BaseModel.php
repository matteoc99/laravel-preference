<?php

namespace Matteoc99\LaravelPreference\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

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

    public function getTable()
    {
        $prefix = config('user_preference.db.table_prefix') ?? "";
        if (!empty($prefix) && !Str::endsWith($prefix, '_')) {
            $prefix .= '_';
        }

        return $prefix . parent::getTable();
    }

}