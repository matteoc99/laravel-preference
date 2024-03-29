<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;

class InRule extends DataRule
{

    public function message()
    {
        return sprintf("One of: %s expected", implode(", ",$this->getData()));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if(!in_array($value, $this->getData())){
            $fail($this->message());
        }
    }
}