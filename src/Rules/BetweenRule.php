<?php

namespace Matteoc99\LaravelPreference\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BetweenRule implements ValidationRule
{
    public function __construct(protected float $min, protected float $max)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_numeric($value)) {
            $fail('A number is expected');
        }
        if ($value < $this->min || $value > $this->max) {
            $fail("The number is expected to be between $this->min and $this->max");
        }
    }
}
