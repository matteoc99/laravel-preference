<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Validation\Rule;

interface CastableEnum extends \BackedEnum
{

    public function validation(): Rule|string;


    public function castToString(mixed $value): string;

    public function castFromString(string $value): mixed;
}