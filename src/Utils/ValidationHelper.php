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
use Matteoc99\LaravelPreference\Rules\InstanceOfRule;
use Matteoc99\LaravelPreference\Rules\OrRule;

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
    public static function validateValue(mixed $value, ?CastableEnum $cast, ?ValidationRule $rule, bool $nullable = false, array|null $allowed_classes = []): void
    {
        $validator = Validator::make(['value' => $value], ['value' => self::getValidationRules($cast, $rule, $nullable, $allowed_classes)]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }
    }

    /**
     * @param Preference $preference
     *
     * @throws ValidationException
     */
    public static function validatePreference(Preference $preference): void
    {
        if (isset($preference->default_value)) {
            self::validateValue(
                $preference->default_value,
                $preference->cast, $preference->rule,
                $preference->nullable,
                $preference->allowed_values
            );
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

        if (!empty($preferenceData['allowed_values'])) {
            self::validateAllowedClasses($preferenceData['cast'], $preferenceData['allowed_values']);
        }

        if (!empty($preferenceData['default_value'])) {
            ValidationHelper::validateValue(
                $preferenceData['default_value'],
                $preferenceData['cast'],
                $preferenceData['rule'] ?? null,
                $preferenceData['nullable'],
                $preferenceData['allowed_values'] ?? [],
            );
        }


        if (array_key_exists('group', $preferenceData)) {
            throw new InvalidArgumentException(
                sprintf("index: #%s group has been deprecated", $index)
            );
        }
    }

    private static function getValidationRules(?CastableEnum $cast, ?ValidationRule $rule, bool $nullable = false, array|null $allowed_classes = []): array
    {
        $rules = [];
        if ($nullable) {
            $rules[] = "nullable";
        }
        if (!empty($allowed_classes)) {
            $instance_rules = [];
            foreach ($allowed_classes as $class) {
                $instance_rules[] = new InstanceOfRule($class);
            }

            $rules[] = new OrRule(...$instance_rules);
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

    /**
     * @param CastableEnum|null $cast
     * @param array             $classes
     *
     * @return void
     */
    public static function validateAllowedClasses(?CastableEnum $cast, array $classes): void
    {
        if (empty($cast) || !($cast instanceof CastableEnum)) {
            throw new InvalidArgumentException(
                sprintf("Cast is required and needs to implement 'CastableEnum'")
            );
        }
        if ($cast->isPrimitive()) {
            throw new InvalidArgumentException("Allowed classes are not supported for primitive casts");
        }

        foreach ($classes as $class) {
            if (!is_string($class) || !class_exists($class)) {
                throw new InvalidArgumentException("All allowed classes must be strings and they must exist.");
            }
        }
    }


}