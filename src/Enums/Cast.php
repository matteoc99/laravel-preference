<?php

namespace Matteoc99\LaravelPreference\Enums;

use BackedEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Rules\InstanceOfRule;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;


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

    case BACKED_ENUM = 'backed_enum';

    public function validation(): ValidationRule|array|string
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
            self::BACKED_ENUM => new InstanceOfRule(BackedEnum::class),
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
            self::BACKED_ENUM => SerializeHelper::deserializeEnum($value),
        };
    }

    /**
     * @throws ValidationException
     */
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
            self::BACKED_ENUM => SerializeHelper::serializeEnum($value),
        };
    }

    /**
     * @throws ValidationException
     */
    private function ensureType(mixed $value): mixed
    {
        return match ($this) {
            self::INT => intval($value),
            self::FLOAT => floatval($value),
            self::STRING => (string)$value,
            self::BOOL => !empty($value),
            self::ARRAY => $this->ensureArray($value),
            self::TIMESTAMP, self::DATETIME, self::DATE => $this->ensureCarbon($value),
            self::TIME => $this->ensureCarbon($value, 'setTimeFromTimeString'),
            self::BACKED_ENUM => $this->ensureBackedEnum($value),
            default => throw ValidationException::withMessages(["Unknown casting type"]),
        };
    }


    private function ensureArray(mixed $value): array
    {
        if (!is_array($value)) {
            $value = json_decode($value, true);
        }
        return $value;
    }

    /**
     * @throws ValidationException
     */
    private function ensureCarbon(mixed $value, string $method = 'parse'): Carbon
    {
        if (!($value instanceof Carbon)) {
            try {
                $value = Carbon::$method($value);
            } catch (Exception $_) {
                throw ValidationException::withMessages([
                    "Invalid format for cast to " . $this->name
                ]);
            }
        }
        return $value;
    }

    /**
     * @throws ValidationException
     */
    private function ensureBackedEnum(mixed $value): BackedEnum
    {
        if (!($value instanceof BackedEnum)) {
            throw ValidationException::withMessages(["Wrong type for Backed enum casting"]);
        }
        return $value;
    }
}