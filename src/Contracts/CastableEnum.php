<?php

namespace Matteoc99\LaravelPreference\Contracts;

use BackedEnum;
use Illuminate\Contracts\Validation\ValidationRule;

interface CastableEnum extends BackedEnum
{

    public function validation(): ValidationRule|array|string|null;


    public function castToString(mixed $value): string;

    public function castFromString(string $value): mixed;


    /**
     * used by the Controller to cast to json
     *
     * @param mixed $value
     *
     * @return array
     */
    public function castToDto(mixed $value): array;

}