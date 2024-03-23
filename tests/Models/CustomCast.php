<?php

namespace Matteoc99\LaravelPreference\Tests\Models;

use Illuminate\Contracts\Validation\Rule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum CustomCast: string implements CastableEnum
{
    case TIMEZONE = 'tz';

    public function validation(): Rule|array|string
    {
        return match ($this) {
            self::TIMEZONE => 'timezone:all',
        };
    }

    public function castFromString(string $value): mixed
    {
        return match ($this) {
            self::TIMEZONE => $value,

        };
    }

    public function castToString(mixed $value): string
    {
        return match ($this) {
            self::TIMEZONE => (string)$value,
        };
    }
}
