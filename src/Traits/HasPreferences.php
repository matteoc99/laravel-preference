<?php

namespace Matteoc99\LaravelPreference\Traits;

use BackedEnum;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;
use UnitEnum;

trait HasPreferences
{
    /** Defines a polymorphic relationship to user preferences. */
    private function userPreferences(): MorphMany
    {
        return $this->morphMany(UserPreference::class, 'preferenceable');
    }

    /**
     * Get a user's preference value or default if not set.
     *
     * @param  mixed|null  $default  Default value if preference not set.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function getPreference(PreferenceGroup|Preference $preference, mixed $default = null): mixed
    {

        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::GET);

        $userPreference = $this->userPreferences()->where('preference_id', $preference->id)->first();

        if (! empty($userPreference) && $preference->nullable) {
            return $userPreference->value;
        }

        return $userPreference?->value ?? $preference->default_value ?? $default;
    }

    /**
     * Get a user's preference value or default if not set with no casting
     *
     * @param  string|null  $default  Default value if preference not set.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function getPreferenceDto(PreferenceGroup|Preference $preference, mixed $default = null): array
    {
        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::GET);

        $value = $this->getPreference($preference, $default);

        return $preference->cast ? $preference->cast->castToDto($value) : ['value' => json_encode($value)];
    }

    /**
     * Set or update a user's preference value.
     *
     * @param  mixed  $value  Value to set for the preference.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     * @throws ValidationException
     */
    public function setPreference(PreferenceGroup|Preference $preference, mixed $value): void
    {

        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::UPDATE);

        $value = $this->restoreOriginalValue($preference, $value);

        ValidationHelper::validateValue(
            $value,
            $preference->cast,
            $preference->rule,
            $preference->nullable,
            $preference->allowed_values
        );

        $this->userPreferences()->updateOrCreate(['preference_id' => $preference->id], ['value' => $value]);
    }

    /**
     * Remove a user's preference.
     *
     *
     * @return int Number of deleted records.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function removePreference(PreferenceGroup|Preference $preference): int
    {

        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::DELETE);

        return $this->userPreferences()->where('preference_id', $preference->id)->delete();
    }

    /**
     * Reset the model to its original state
     * Remove all user's preferences.
     *
     *
     * @return int Number of deleted records.
     *
     * @throws AuthorizationException
     */
    public function removeAllPreferences(): int
    {
        $this->authorize(PolicyAction::DELETE_ALL);

        return $this->userPreferences()->delete();
    }

    /**
     * Get all preferences for a user, optionally filtered by group.
     *
     * @param  string|null  $group  Group to filter preferences by.
     *
     * @throws AuthorizationException
     */
    public function getPreferences(?string $group = null): Collection
    {
        $this->authorize(PolicyAction::INDEX);

        $query = $this->userPreferences()->with('preference');

        if ($group) {
            $query->whereHas('preference', fn ($query) => $query->where('group', $group));
        }

        return $query->get();
    }

    /**
     * Validate existence of a preference and retrieve it.
     *
     * @param  PreferenceGroup|Preference  $preference  Preference name.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    private function validateAndRetrievePreference(PreferenceGroup|Preference $preference, PolicyAction $action): Preference
    {

        $this->authorize($action);

        if (! $preference instanceof Preference) {

            SerializeHelper::conformNameAndGroup($preference, $group);

            /** @var Preference $preference * */
            $preference = Preference::where('group', $group)->where('name', $preference)->first();
        }
        if (! $preference) {
            throw new PreferenceNotFoundException("Preference not found: $preference in group $group");
        }

        if (! empty($preference->policy)) {
            $policy = $preference->policy;
            $authorized = false;

            $enum = SerializeHelper::reversePreferenceToEnum($preference);

            if ($policy instanceof PreferencePolicy) {
                $authorized = match ($action) {
                    PolicyAction::INDEX => $policy->index(Auth::user(), $this, $enum),
                    PolicyAction::GET => $policy->get(Auth::user(), $this, $enum),
                    PolicyAction::UPDATE => $policy->update(Auth::user(), $this, $enum),
                    PolicyAction::DELETE => $policy->delete(Auth::user(), $this, $enum),
                    default => throw new AuthorizationException('Unknown Policy: '.$action->name),
                };
            }

            if (! $authorized) {
                throw new AuthorizationException('The user is not authorized to perform the action: '.$action->name);
            }

        }

        return $preference;
    }

    /** @throws AuthorizationException */
    private function authorize(PolicyAction $action): void
    {
        if (! $this->isUserAuthorized(Auth::user(), $action)) {
            throw new AuthorizationException('The user is not authorized to perform the action: '.$action->name);
        }
    }

    private function restoreOriginalValue(Preference $preference, mixed $value): mixed
    {
        if (! empty($preference->allowed_values)) {
            foreach ($preference->allowed_values as $allowedClass) {

                if (! is_string($value)) {
                    continue;
                }

                if (! class_exists($allowedClass)) {
                    throw new InvalidArgumentException("Class $allowedClass does not exist.");
                }

                if (in_array(BackedEnum::class, class_implements($allowedClass))) {
                    $val = $allowedClass::tryFrom($value);
                    if (! empty($val)) {
                        return $val;
                    }
                }

                if (in_array(UnitEnum::class, class_implements($allowedClass))) {
                    if (defined("$allowedClass::$value")) {
                        return constant("$allowedClass::$value");
                    }
                }
            }

        }

        return $value;
    }
}
