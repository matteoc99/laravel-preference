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

    private function userPreferences(): MorphMany
    {
        return $this->morphMany(UserPreference::class, 'preferenceable');
    }

    /**
     * Retrieve a preference value, prioritizing user settings, then defaults.
     *
     * @param PreferenceGroup $name
     * @param mixed|null      $default
     *
     * @return mixed
     * @throws PreferenceNotFoundException
     */
    public function getPreference(PreferenceGroup $name, mixed $default = null): mixed
    {
        $this->authorize(PolicyAction::GET);
        SerializeHelper::conformNameAndGroup($name, $group);
        /**@var string $name * */
        $preference = $this->validateAndRetrievePreference($name, $group);

        $userPreference = $this->userPreferences()
            ->where('preference_id', $preference->id)
            ->first();

        return $userPreference?->value ?? $this->getDefaultPreferenceValue($name, $group) ?? $default;
    }

    /**
     * Set a preference value, handling validation and persistence.
     *
     * @param PreferenceGroup $name
     * @param mixed           $value
     *
     * @throws ValidationException
     * @throws PreferenceNotFoundException
     */
    public function setPreference(PreferenceGroup $name, mixed $value): void
    {
        $this->authorize(PolicyAction::UPDATE);

        SerializeHelper::conformNameAndGroup($name, $group);
        /**@var string $name * */
        $preference = $this->validateAndRetrievePreference($name, $group);

        $validator = Validator::make(['value' => $value], ['value' => $preference->getValidationRules()]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->userPreferences()->updateOrCreate([
            'preference_id' => $preference->id,
        ], ['value' => $value]);
    }

    /**
     * Remove a preference for the current user.
     *
     * @param PreferenceGroup $name
     *
     * @return int
     * @throws PreferenceNotFoundException
     */
    public function removePreference(PreferenceGroup $name): int
    {
        $this->authorize(PolicyAction::DELETE);

        SerializeHelper::conformNameAndGroup($name, $group);
        /**@var string $name * */
        $preference = $this->validateAndRetrievePreference($name, $group);

        return $this->userPreferences()->where('preference_id', $preference->id)->delete();
    }

    /**
     * Retrieve all preferences for the current user, optionally filtered by group.
     *
     * @param string|null $group
     *
     * @return Collection
     */
    public function getPreferences(string $group = null): Collection
    {
        $this->authorize(PolicyAction::INDEX);

        $query = $this->userPreferences()->with('preference');

        if ($group) {
            $query->whereHas('preference', function ($query) use ($group) {
                $query->where('group', $group);
            });
        }

        return $query->get();
    }

    private function validateAndRetrievePreference(string $name, string $group): Preference
    {
        /**@var Preference $preference * */
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        if (!$preference) {
            throw new PreferenceNotFoundException("Preference not found: $name $group ");
        }
        return $preference;
    }


    private function getDefaultPreferenceValue(string $name, string $group): mixed
    {
        return Preference::where('group', $group)->where('name', $name)->first()?->default_value ?? null;
    }

    private function authorize(PolicyAction $action): void
    {
        if (!$this->isUserAuthorized(Auth::user(), $action)) {
            throw new AuthorizationException("the user is not authorized to perform the action: " . $action->name);
        }
    }
}
