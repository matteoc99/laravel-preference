<?php

namespace Matteoc99\LaravelPreference\Utils;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Models\Preference;

class ValidationHelper
{

    /**
     * @param mixed               $value
     * @param CastableEnum|null   $cast
     * @param ValidationRule|null $rule
     * @param bool                $nullable
     *
     * @throws ValidationException
     */
    public static function validateValue(mixed $value, ?CastableEnum $cast, ?ValidationRule $rule, bool $nullable = false): void
    {
        $validator = Validator::make(['value' => $value], ['value' => self::getValidationRules($cast, $rule, $nullable)]);

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
        if (isset($preference->default_value)) {
            self::validateValue($preference->default_value, $preference->cast, $preference->rule, $preference->nullable);
        }
    }

    /**
     * @param array $preferenceData
     * @param int   $index
     *
     * @throws ValidationException
     */
    public static function validatePreferenceData(array $preferenceData, int $index): void
    {
        if (empty($preferenceData['name']) || !($preferenceData['name'] instanceof PreferenceGroup)) {
            throw new InvalidArgumentException(
                sprintf("index: #%s name is required and needs to be a PreferenceGroup", $index)
            );
        }
        if (empty($preferenceData['cast']) || !($preferenceData['cast'] instanceof CastableEnum)) {
            throw new InvalidArgumentException(
                sprintf("index: #%s cast is required and needs to implement 'CastableEnum'", $index)
            );
        }
        if (!empty($preferenceData['rule']) && !$preferenceData['rule'] instanceof ValidationRule) {
            throw new InvalidArgumentException(
                sprintf("index: #%s validation rule musst implement ValidationRule", $index)
            );
        }
        if (!empty($preferenceData['policy']) && !$preferenceData['policy'] instanceof PreferencePolicy) {
            throw new InvalidArgumentException(
                sprintf("index: #%s policy musst implement PreferencePolicy", $index)
            );
        }

        if (!empty($preferenceData['default_value'])) {
            ValidationHelper::validateValue(
                $preferenceData['default_value'],
                $preferenceData['cast'],
                $preferenceData['rule'] ?? null,
                $preferenceData['nullable'],
            );
        }


        if (array_key_exists('group', $preferenceData)) {
            throw new InvalidArgumentException(
                sprintf("index: #%s group has been deprecated", $index)
            );
        }
    }

    private static function getValidationRules(?CastableEnum $cast, ?ValidationRule $rule, bool $nullable = false): array
    {
        $rules = [];
        if ($nullable) {
            $rules[] = "nullable";
        }

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