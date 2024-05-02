<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class AndRule implements ValidationRule
{

    private array $rules;

    public function __construct(...$rules)
    {
        $this->rules = $rules;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $anyFails     = false;
        $errorMessage = "";

        foreach ($this->rules as $index => $rule) {
            $validator = Validator::make([$attribute => $value], [$attribute => $rule]);

            if ($validator->fails()) {
                $anyFails = true;

                $messages        = $validator->messages();
                $errorMessage = "Rule $index: " . $messages->first($attribute);

                break;
            }
        }

        if ($anyFails) {
            $fail($errorMessage);
        }
    }
}