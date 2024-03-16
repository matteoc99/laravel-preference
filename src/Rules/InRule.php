<?php

namespace Matteoc99\LaravelPreference\Rules;

use Illuminate\Support\Str;

class InRule extends DataRule
{

    public function passes($attribute, $value)
    {

        return in_array($value, $this->getData());
    }

    public function message()
    {
        return sprintf("One of: %s expected", implode(", ",$this->getData()));
    }
}