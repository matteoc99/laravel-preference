<?php

namespace Matteoc99\LaravelPreference\Enums;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;


enum Cast: string implements CastableEnum
{
    case INT = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOL = 'bool';
    case ARRAY = 'array';
    case DATE = 'date';
    case DATETIME = 'datetime';
    case TIMESTAMP = 'timestamp';

    public function validation(): Rule|string
    {
        return match ($this) {
            self::INT => 'integer',
            self::FLOAT => 'numeric',
            self::STRING => 'string',
            self::BOOL => 'boolean',
            self::ARRAY => 'array',
            self::DATE, self::DATETIME => 'date',
            self::TIMESTAMP => 'date_format:U',
        };
    }

    public function cast(mixed $value): mixed
    {
        return match ($this) {
            self::INT => (int)$value,
            self::FLOAT => (float)$value,
            self::STRING => (string)$value,
            self::BOOL => !empty($value),
            self::ARRAY => (array)$value,
            self::DATE, self::DATETIME => new Carbon($value),
            self::TIMESTAMP => Carbon::createFromTimestamp($value),
        };
    }
}