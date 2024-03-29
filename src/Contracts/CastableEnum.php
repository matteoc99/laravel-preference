<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Contracts\Validation\ValidationRule;

interface CastableEnum extends \BackedEnum
{

    public function validation(): ValidationRule|array|string;


    public function castToString(mixed $value): string;

    public function castFromString(string $value): mixed;
}