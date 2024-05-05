<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class LaravelRule implements ValidationRule
{
    public function __construct(protected string $rule)
    {
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {

        $validator = Validator::make([$attribute => $value], [$attribute => $this->rule]);

        if ($validator->fails()) {
            $fail($validator->messages());
        }
    }
}