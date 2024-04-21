<?php

namespace Matteoc99\LaravelPreference\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;

class OrRule implements ValidationRule
{

    private array $rules;

    public function __construct(...$rules)
    {
        $this->rules = $rules;
    }


    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $anyPasses = false;
        $errorMessages = [];

        foreach ($this->rules as $index => $rule) {
            $validator = Validator::make([$attribute => $value], [$attribute => $rule]);

            if ($validator->passes()) {
                $anyPasses = true;
                break;
            }

            $messages = $validator->messages();
            $errorMessages[] = "Rule " . ($index + 1) . ": " . $messages->first($attribute);
        }

        if (!$anyPasses) {
            $errorMessage = "The value for '$attribute' does not match any of the required rules:\n" . implode("\n", $errorMessages);
            $fail($errorMessage);
        }
    }
}