<?php

namespace Matteoc99\LaravelPreference\Factory;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Exceptions\InvalidStateException;
use Matteoc99\LaravelPreference\Factory\builders\BaseBuilder;
use Matteoc99\LaravelPreference\Factory\builders\ObjectPreferenceBuilder;
use Matteoc99\LaravelPreference\Factory\builders\PrimitivePreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

class PreferenceBuilder
{
    /**
     * Convenience method to create a new string preference with optional default value.
     * Initializes the preference as nullable with a default value of null or as specified.
     * This method uses a fluent interface to configure and create the preference in one call.
     *
     * @param  PreferenceGroup  $name  The preference enum, used as the identifier.
     * @param  string|null  $default  Optional. The default value for the preference. Defaults to null.
     * @return void Creates the preference and commits it to the database.
     *
     * @throws InvalidStateException
     *
     * @example
     * ```
     * PreferenceBuilder::buildString(UserPreference::TEST, "default");
     * ```
     * This example creates a string preference for 'UserPreference::TEST' with a default value of "default".
     */
    public static function buildString(PreferenceGroup $name, ?string $default = null): void
    {
        self::init($name)->nullable()->withDefaultValue($default)->create();
    }

    /**
     * Convenience method to create a new array preference with optional default value.
     * Initializes the preference as nullable with a default value of null or as specified, and sets the cast type to
     * ARRAY. This method uses a fluent interface to configure and create the preference in one call.
     *
     * @param  PreferenceGroup  $name  The preference enum, used as the identifier.
     * @param  array|null  $default  Optional. The default value for the preference. Defaults to null.
     * @return void Creates the preference and commits it to the database.
     *
     * @throws InvalidStateException
     *
     * @example
     * ```
     * PreferenceBuilder::buildArray(new SettingsPreferenceGroup(), ["item1", "item2"]);
     * ```
     * This example creates an array preference for 'SettingsPreferenceGroup' with a default array containing "item1"
     * and "item2".
     */
    public static function buildArray(PreferenceGroup $name, ?array $default = null): void
    {
        self::init($name, Cast::ARRAY)->nullable()->withDefaultValue($default)->create();
    }

    /**
     * Initializes the PreferenceBuilder with a specified name and cast type, setting up a new preference entry.
     * The method sets the initial configuration for the preference using a fluent builder interface, allowing further
     * customization.
     *
     * @param  PreferenceGroup  $name  Required. The preference enum, which also determines its name. Must be an
     *                                 instance of PreferenceGroup.
     * @param  CastableEnum  $cast  Optional. Specifies the data type the preference value should be cast to.
     *                              Defaults to Cast::STRING.
     * @return PrimitivePreferenceBuilder|ObjectPreferenceBuilder Depending on weather the cast is primitive or not
     *
     * @example
     * ```
     * $preference = PreferenceBuilder::init(Preference::TEST, Cast::INTEGER)
     * ->withDefaultValue(12)
     * ->create();
     * ```
     * This example initializes a preference with a default integer value and then creates it.
     */
    public static function init(PreferenceGroup $name, CastableEnum $cast = Cast::STRING): PrimitivePreferenceBuilder|ObjectPreferenceBuilder
    {

        $builderClass = match (! method_exists($cast, 'isPrimitive') || $cast->isPrimitive()) {
            true => PrimitivePreferenceBuilder::class,
            false => ObjectPreferenceBuilder::class,
        };

        return new $builderClass($name, $cast);
    }

    /** Deletes a preference from the DB based on its name */
    public static function delete(PreferenceGroup $name): int
    {
        SerializeHelper::conformNameAndGroup($name, $group);

        return Preference::where('group', $group)->where('name', $name)->delete();
    }

    /**
     * Initializes bulk preferences with specified configurations and validations.
     *
     * @param  array  $preferences  An array of associative arrays where each associative array
     *                              represents the configuration for a preference. Each
     *                              preference array can contain:
     *                              - 'name' (required): The name of the preference, which should
     *                              be a valid PreferenceGroup.
     *                              - 'cast' (optional): A CastableEnum value that specifies how
     *                              the value should be casted. Default is Cast::STRING if not
     *                              provided.
     *                              - 'nullable' (optional): A boolean indicating if the
     *                              preference can be null. Inherits the function's $nullable
     *                              parameter if not specified.
     *                              - 'default_value' (optional): The default value for the
     *                              preference.
     *                              - 'description' (optional): A description of the preference.
     *                              Default is an empty string.
     *                              - 'policy' (optional): A PreferencePolicy that applies to the
     *                              preference.
     *                              - 'rule' (optional): A ValidationRule that the preference
     *                              must comply with.
     *                              - 'allowed_values' (optional) for objects & enums,
     *                              restrict the allowed classes. array of strings
     * @param  bool  $nullable  A boolean value indicating if all preferences can be set to
     *                          null. Defaults to false. This value is used as the default
     *                          for 'nullable' in each preference configuration if 'nullable'
     *                          is not explicitly set in the preference array.
     *
     * @throws ValidationException if any preference data fails validation checks.
     * @throws InvalidArgumentException if the preferences array is empty, or if required data like 'name' or 'cast'
     *                                  are missing, or do not meet the expected types or constraints.
     * @throws InvalidStateException
     */
    public static function initBulk(array $preferences, bool $nullable = false): void
    {
        if (empty($preferences)) {
            throw new InvalidArgumentException('no preferences provided');
        }

        $cleanPreferences = [];

        foreach ($preferences as $index => $preferenceData) {

            if ($preferenceData instanceof BaseBuilder) {
                if ($preferenceData->isStateSet(BaseBuilder::STATE_CREATED)) {
                    throw new InvalidStateException($preferenceData->getState(), 'The State should not be Created at this point, as its initBulk responsibility');
                }
                if (! $preferenceData->isStateSet(BaseBuilder::STATE_NULLABLE_SET)) {
                    $preferenceData->nullable($nullable);
                }

                $preferenceData->updateOrCreate();

                continue;
            }

            if (empty($preferenceData['cast'])) {
                $preferenceData['cast'] = Cast::STRING;
            }
            if (! array_key_exists('nullable', $preferenceData)) {
                $preferenceData['nullable'] = $nullable;
            }

            ValidationHelper::validatePreferenceData($preferenceData, $index);

            SerializeHelper::conformNameAndGroup($preferenceData['name'], $preferenceData['group']);

            if (array_key_exists('rule', $preferenceData)) {
                $preferenceData['rule'] = serialize($preferenceData['rule']);
            }
            if (array_key_exists('default_value', $preferenceData)) {
                $valueCaster = new ValueCaster($preferenceData['cast']);
                $preferenceData['default_value'] = $valueCaster->set(null, '', $preferenceData['default_value'], []);
            }

            if (array_key_exists('allowed_values', $preferenceData) && is_array($preferenceData['allowed_values'])) {
                $preferenceData['allowed_values'] = json_encode($preferenceData['allowed_values']);
            }

            $preferenceData['cast'] = serialize($preferenceData['cast']);

            // Ensure Defaults
            $preferenceData = array_merge([
                'group' => 'general',
                'default_value' => null,
                'allowed_values' => null,
                'description' => '',
                'policy' => null,
                'rule' => null,
                'nullable' => false,
            ], $preferenceData);
            $cleanPreferences[] = $preferenceData;
        }

        Preference::upsert($cleanPreferences, ['name', 'group']);
    }

    /**
     * Deletes a bulk of preferences based on the provided configuration data.
     *
     * @param  array  $preferences  An array of associative arrays where each associative array represents
     *                              a preference to be deleted. Each preference configuration should include:
     *                              - 'name' (required): The name of the preference, which should be an instance of
     *                              PreferenceGroup. The name is mandatory and must implement PreferenceGroup.
     * @return int Returns the number of deleted preferences.
     *
     * @throws InvalidArgumentException if the preferences array is empty or if any preference lacks a required
     * @throws InvalidStateException
     *                               'name' field, or if the 'name' field does not implement PreferenceGroup.
     */
    public static function deleteBulk(array $preferences): int
    {
        if (empty($preferences)) {
            throw new InvalidArgumentException('no preferences provided');
        }
        $query = Preference::query();

        foreach ($preferences as $index => $preferenceData) {

            if ($preferenceData instanceof BaseBuilder) {
                if ($preferenceData->isStateSet(BaseBuilder::STATE_DELETED)) {
                    throw new InvalidStateException($preferenceData->getState(), "The State should not be Deleted at this point, as its deleteBulk's responsibility");
                }

                $preferenceData->delete();

                continue;
            }

            if (empty($preferenceData['name']) || ! ($preferenceData['name'] instanceof PreferenceGroup)) {
                throw new InvalidArgumentException(
                    sprintf('index: #%s name is required and must implement PreferenceGroup', $index)
                );
            }

            SerializeHelper::conformNameAndGroup($preferenceData['name'], $preferenceData['group']);

            $query->orWhere(function (Builder $query) use ($preferenceData) {
                $query->where('name', $preferenceData['name']);
                $query->where('group', $preferenceData['group']);
            });
        }

        return $query->delete();
    }
}
