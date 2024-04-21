<?php

namespace Matteoc99\LaravelPreference\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

trait HasPreferences
{
    /**
     * Defines a polymorphic relationship to user preferences.
     *
     * @return MorphMany
     */
    private function userPreferences(): MorphMany
    {
        return $this->morphMany(UserPreference::class, 'preferenceable');
    }

    /**
     * Get a user's preference value or default if not set.
     *
     * @param PreferenceGroup|Preference $preference
     * @param mixed|null                 $default Default value if preference not set.
     *
     * @return mixed
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function getPreference(PreferenceGroup|Preference $preference, mixed $default = null): mixed
    {


        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::GET);

        $userPreference = $this->userPreferences()->where('preference_id', $preference->id)->first();

        if (!empty($userPreference) && $preference->nullable) {
            return $userPreference->value;
        }

        return $userPreference?->value ?? $preference->default_value ?? $default;
    }

    /**
     * Get a user's preference value or default if not set with no casting
     *
     * @param PreferenceGroup|Preference $preference
     * @param string|null                $default Default value if preference not set.
     *
     * @return array
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
     * @param PreferenceGroup|Preference $preference
     * @param mixed                      $value Value to set for the preference.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     * @throws ValidationException
     */
    public function setPreference(PreferenceGroup|Preference $preference, mixed $value): void
    {

        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::UPDATE);

        ValidationHelper::validateValue(
            $value,
            $preference->cast,
            $preference->rule,
            $preference->nullable
        );

        $this->userPreferences()->updateOrCreate(['preference_id' => $preference->id], ['value' => $value]);
    }

    /**
     * Remove a user's preference.
     *
     * @param PreferenceGroup|Preference $preference
     *
     * @return int Number of deleted records.
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function removePreference(PreferenceGroup|Preference $preference): int
    {

        $preference = $this->validateAndRetrievePreference($preference, PolicyAction::DELETE);


        return $this->userPreferences()->where('preference_id', $preference->id)->delete();
    }

    /**
     * Get all preferences for a user, optionally filtered by group.
     *
     * @param string|null $group Group to filter preferences by.
     *
     * @return Collection
     * @throws AuthorizationException
     */
    public function getPreferences(string $group = null): Collection
    {
        $this->authorize(PolicyAction::INDEX);

        $query = $this->userPreferences()->with('preference');

        if ($group) {
            $query->whereHas('preference', fn($query) => $query->where('group', $group));
        }

        return $query->get();
    }

    /**
     * Validate existence of a preference and retrieve it.
     *
     * @param PreferenceGroup|Preference $preference Preference name.
     * @param PolicyAction               $action
     *
     * @return Preference
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    private function validateAndRetrievePreference(PreferenceGroup|Preference $preference, PolicyAction $action): Preference
    {

        $this->authorize($action);

        if (!$preference instanceof Preference) {

            SerializeHelper::conformNameAndGroup($preference, $group);

            /**@var Preference $preference * */
            $preference = Preference::where('group', $group)->where('name', $preference)->first();
        }
        if (!$preference) {
            throw new PreferenceNotFoundException("Preference not found: $preference in group $group");
        }

        if (!empty($preference->policy)) {
            $policy     = $preference->policy;
            $authorized = false;

            $enum = SerializeHelper::reversePreferenceToEnum($preference);

            if ($policy instanceof PreferencePolicy) {
                $authorized = match ($action) {
                    PolicyAction::INDEX => $policy->index(Auth::user(), $this, $enum),
                    PolicyAction::GET => $policy->get(Auth::user(), $this, $enum),
                    PolicyAction::UPDATE => $policy->update(Auth::user(), $this, $enum),
                    PolicyAction::DELETE => $policy->delete(Auth::user(), $this, $enum),
                    default => throw new AuthorizationException("Unknown Policy: " . $action->name),
                };
            }

            if (!$authorized) {
                throw new AuthorizationException("The user is not authorized to perform the action: " . $action->name);
            }

        }

        return $preference;
    }

    /**
     * @param PolicyAction $action
     *
     * @throws AuthorizationException
     */
    private function authorize(PolicyAction $action): void
    {
        if (!$this->isUserAuthorized(Auth::user(), $action)) {
            throw new AuthorizationException("The user is not authorized to perform the action: " . $action->name);
        }
    }
}
