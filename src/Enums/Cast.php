<?php

namespace Matteoc99\LaravelPreference\Enums;

use BackedEnum;
use Carbon\Carbon;
use Exception;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Rules\InstanceOfRule;
use Matteoc99\LaravelPreference\Rules\IsRule;
use UnitEnum;


enum Cast: string implements CastableEnum
{
    case NONE = 'none';
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
    case ENUM = 'enum';
    case OBJECT = 'object';

    public function validation(): ValidationRule|array|string|null
    {
        return match ($this) {
            self::NONE => null,
            self::INT => 'integer',
            self::FLOAT => 'numeric',
            self::STRING => 'string',
            self::BOOL => 'boolean',
            self::ARRAY => 'array',
            self::DATE, self::DATETIME => 'date',
            self::TIME => 'date_format:H:i',
            self::TIMESTAMP => 'date_format:U',
            self::BACKED_ENUM => new InstanceOfRule(BackedEnum::class),
            self::ENUM => new InstanceOfRule(UnitEnum::class),
            self::OBJECT => new IsRule(Type::OBJECT),
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
            self::NONE, self::BACKED_ENUM, self::ENUM, self::OBJECT => unserialize($value),
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
            self::NONE, self::BACKED_ENUM, self::ENUM, self::OBJECT => serialize($value),
        };
    }

    /**
     * @throws ValidationException
     */
    private function ensureType(mixed $value): mixed
    {
        return match ($this) {
            self::NONE => $value,
            self::INT => intval($value),
            self::FLOAT => floatval($value),
            self::STRING => (string)$value,
            self::BOOL => !empty($value),
            self::ARRAY => $this->ensureArray($value),
            self::TIMESTAMP, self::DATETIME, self::DATE, self::TIME => $this->ensureCarbon($value),
            self::BACKED_ENUM => $this->ensureBackedEnum($value),
            self::ENUM => $this->ensureEnum($value),
            self::OBJECT => $this->ensureObject($value),
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
    private function ensureCarbon(mixed $value): Carbon
    {
        if (!($value instanceof Carbon)) {
            try {
                $value = Carbon::parse($value);
            } catch (Exception $_) {
                throw ValidationException::withMessages(["Invalid format for cast to " . $this->name]);
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

    /**
     * @throws ValidationException
     */
    private function ensureEnum(mixed $value): UnitEnum
    {
        if (!($value instanceof UnitEnum)) {
            throw ValidationException::withMessages(["Wrong type for enum casting"]);
        }
        return $value;
    }

    private function ensureObject(mixed $value)
    {
        if (!is_object($value)) {
            throw ValidationException::withMessages(["Wrong type for object casting"]);
        }
        return $value;
    }
}