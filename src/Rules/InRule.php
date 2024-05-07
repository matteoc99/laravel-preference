<?php

namespace Matteoc99\LaravelPreference\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class InRule implements ValidationRule
{
    protected array $data;

    public function __construct(...$data)
    {
        $this->data = $data;
    }

    public function message(): string
    {
        return sprintf('One of: %s expected', implode(', ', $this->data));
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! in_array($value, $this->data)) {
            $fail($this->message());
        }
    }
}
