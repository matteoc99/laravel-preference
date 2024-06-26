<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Models;

use Matteoc99\LaravelPreference\Contracts\CastableEnum;

enum CustomCast: string implements CastableEnum
{
    case TIMEZONE = 'tz';

    public function validation(): array|string
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
            self::TIMEZONE => (string) $value,
        };
    }

    public function castToDto(mixed $value): array
    {
        return ['value' => $value];
    }
}
