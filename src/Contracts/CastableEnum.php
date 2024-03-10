<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Validation\Rule;

interface CastableEnum extends \StringBackedEnum
{
    public function cast(mixed $value): mixed;

    public function validation(): Rule|string;

}