<?php

namespace Matteoc99\LaravelPreference\Traits;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;

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
     * @param PreferenceGroup $name
     * @param mixed|null      $default Default value if preference not set.
     *
     * @return mixed
     * @throws PreferenceNotFoundException|AuthorizationException
     */
    public function getPreference(PreferenceGroup $name, mixed $default = null): mixed
    {
        $this->authorize(PolicyAction::GET);

        $preference = $this->validateAndRetrievePreference($name);

        $userPreference = $this->userPreferences()->where('preference_id', $preference->id)->first();

        return $userPreference?->value ?? $preference->default_value ?? $default;
    }

    /**
     * Set or update a user's preference value.
     *
     * @param PreferenceGroup $name
     * @param mixed           $value Value to set for the preference.
     *
     * @throws PreferenceNotFoundException|AuthorizationException|ValidationException
     */
    public function setPreference(PreferenceGroup $name, mixed $value): void
    {
        $this->authorize(PolicyAction::UPDATE);


        $preference = $this->validateAndRetrievePreference($name);

        $validator = Validator::make(['value' => $value], ['value' => $preference->getValidationRules()]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->userPreferences()->updateOrCreate(['preference_id' => $preference->id], ['value' => $value]);
    }

    /**
     * Remove a user's preference.
     *
     * @param PreferenceGroup $name
     *
     * @return int Number of deleted records.
     * @throws PreferenceNotFoundException|AuthorizationException
     */
    public function removePreference(PreferenceGroup $name): int
    {
        $this->authorize(PolicyAction::DELETE);

        $preference = $this->validateAndRetrievePreference($name);

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
     * @param PreferenceGroup $name Preference name.
     *
     * @return Preference
     * @throws PreferenceNotFoundException If preference not found.
     */
    private function validateAndRetrievePreference(PreferenceGroup $name): Preference
    {
        SerializeHelper::conformNameAndGroup($name, $group);

        /**@var string $name * */
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        if (!$preference) {
            throw new PreferenceNotFoundException("Preference not found: $name in group $group");
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
