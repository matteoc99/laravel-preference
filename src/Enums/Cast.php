<?php

namespace Matteoc99\LaravelPreference\Enums;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use InvalidArgumentException;
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

    public function castFromString(string $value): mixed
    {
        return match ($this) {
            self::INT => (int)$value,
            self::FLOAT => (float)$value,
            self::STRING => $value,
            self::BOOL => !empty($value),
            self::ARRAY => json_encode($value, 1),
            self::DATE, self::DATETIME => new Carbon($value),
            self::TIMESTAMP => Carbon::createFromTimestamp($value),
        };
    }

    public function castToString(mixed $value): string
    {
        $this->ensureType($value);

        return match ($this) {
            self::BOOL, self::INT, self::FLOAT, self::STRING => (string)$value,
            self::ARRAY => json_encode($value),
            self::DATE => $value->toDateString(),
            self::DATETIME => $value->toDateTimeString(),
            self::TIMESTAMP => $value->timestamp,
        };
    }

    private function ensureType(mixed $value): void
    {
        $type = gettype($value);

        switch ($this) {
            case self::INT:
                if ($type !== 'integer') {
                    throw new InvalidArgumentException("Expected an integer for cast INT, got $type");
                }
                break;
            case self::FLOAT:
                if (!in_array($type, ['double', 'float'])) {
                    throw new InvalidArgumentException("Expected a float or double for cast FLOAT, got $type");
                }
                break;
            case self::STRING:
                if ($type !== 'string') {
                    throw  new InvalidArgumentException("Expected a string for cast STRING, got $type");
                }
                break;
            case self::BOOL:
                if ($type !== 'boolean') {
                    throw new InvalidArgumentException("Expected a boolean for cast BOOL, got $type");
                }
                break;
            case self::ARRAY:
                if ($type !== 'array') {
                    throw new InvalidArgumentException("Expected an array for cast ARRAY, got $type");
                }
                break;
            case self::DATETIME:
            case self::TIMESTAMP:
            case self::DATE:
                if (!($value instanceof Carbon)) {
                    throw new InvalidArgumentException("Expected a Carbon instance to cast, got $type");
                }
                break;
            default:
                throw new InvalidArgumentException("Unknown casting type");
        }
    }
}