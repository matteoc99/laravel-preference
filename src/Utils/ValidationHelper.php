<?php

namespace Matteoc99\LaravelPreference\Utils;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Models\Preference;

class ValidationHelper
{

    /**
     * @param mixed               $value
     * @param CastableEnum|null   $cast
     * @param ValidationRule|null $rule
     *
     * @throws ValidationException
     */
    public static function validateValue(mixed $value, ?CastableEnum $cast, ?ValidationRule $rule): void
    {
        $validator = Validator::make(['value' => $value], ['value' => self::getValidationRules($cast, $rule)]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param Preference $preference
     *
     * @throws ValidationException
     */
    public static function validatePreference(Preference $preference)
    {
        self::validateValue($preference->default_value, $preference->cast, $preference->rule);
    }

    private static function getValidationRules(?CastableEnum $cast, ?ValidationRule $rule): array
    {
        $rules = [];
        if ($cast) {
            $castValidation = $cast->validation();
            if ($castValidation) {
                $rules = array_merge($rules, self::processRule($castValidation));
            }
        }
        if ($rule) {
            $rules = array_merge($rules, self::processRule($rule));
        }

        return $rules;
    }

    private static function processRule($rule): array
    {
        if (is_array($rule)) {
            return $rule;
        } elseif ($rule instanceof ValidationRule) {
            return [$rule];
        } else {
            return explode('|', $rule);
        }
    }


}