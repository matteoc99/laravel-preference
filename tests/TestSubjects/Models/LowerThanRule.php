<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Models;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class LowerThanRule implements ValidationRule
{
    public function __construct(protected int $value) { }

    public function message()
    {
        return sprintf("A value lower than  '%d' expected", $this->value);
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_int($value) || $value > $this->value) {
            $fail($this->message());
        }
    }
}