<?php

namespace Matteoc99\LaravelPreference\Enums;

use Carbon\Carbon;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;


enum Cast: string implements CastableEnum
{
    case INT = 'int';
    case FLOAT = 'float';
    case STRING = 'string';
    case BOOL = 'bool';
    case ARRAY = 'array';
    case DATE = 'date';
    case TIME = 'time';
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
            self::TIME => 'date_format:H:i',
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
            self::ARRAY => json_decode($value, 1),
            self::DATE, self::DATETIME => new Carbon($value),
            self::TIME => Carbon::now()->setTimeFromTimeString($value),
            self::TIMESTAMP => Carbon::createFromTimestamp($value),
        };
    }

    public function castToString(mixed $value): string
    {
        $value = $this->ensureType($value);

        return match ($this) {
            self::BOOL, self::INT, self::FLOAT, self::STRING => (string)$value,
            self::ARRAY => json_encode($value),
            self::DATE => $value->toDateString(),
            self::DATETIME => $value->toDateTimeString(),
            self::TIMESTAMP => $value->timestamp,
            self::TIME => $value->toTimeString(),
        };
    }

    private function ensureType(mixed $value): mixed
    {

        switch ($this) {
            case self::INT:
                $value = intval($value);
                break;
            case self::FLOAT:
                $value = floatval($value);
                break;
            case self::STRING:
                $value = (string)$value;
                break;
            case self::BOOL:
                return !empty($value);
            case self::ARRAY:
                if (!is_array($value)) {
                    $value = json_decode($value, true);
                }
                break;
            case self::TIMESTAMP:
                if (!($value instanceof Carbon)) {
                    $value = Carbon::createFromTimestamp($value);
                }
            case self::DATETIME:
            case self::DATE:
                if (!($value instanceof Carbon)) {
                    try {
                        $value = Carbon::parse($value);  // Attempt to parse various date/time formats
                    } catch (\Exception $e) {
                        throw ValidationException::withMessages(["Invalid format for cast to DATETIME, DATE, or TIME"]);
                    }
                }
            case self::TIME:
                if (!($value instanceof Carbon)) {
                    $value = Carbon::now()->setTimeFromTimeString($value);
                }
                break;
            default:
                throw ValidationException::withMessages(["Unknown casting type"]);
        }
        return $value;
    }
}