<?php

namespace Matteoc99\LaravelPreference\Tests\Models;

use Closure;
use Matteoc99\LaravelPreference\Rules\DataRule;

class LowerThanRule extends DataRule
{

    public function passes($attribute, $value)
    {
        return is_int($value) && $value < $this->getData()[0];
    }

    public function message()
    {
        return sprintf("One of: %s expected", implode(", ", $this->getData()));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!$this->passes($attribute, $value)) {
            $fail($this->message());
        }
    }
}