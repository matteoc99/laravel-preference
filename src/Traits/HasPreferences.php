<?php

namespace Matteoc99\LaravelPreference\Traits;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;

trait HasPreferences
{

    private function userPreferences(): MorphMany
    {
        return $this->morphMany(UserPreference::class, 'preferenceable');
    }

    /**
     * Retrieve a preference value, prioritizing user settings, then defaults.
     *
     * @param string     $name
     * @param string     $group
     * @param mixed|null $default
     *
     * @return mixed
     */
    public function getPreference(string $name, string $group = 'general', mixed $default = null)
    {
        $userPreference = $this->userPreferences()->with('preference')
            ->whereHas('preference', function ($query) use ($group, $name) {
                $query->where('group', $group)->where('name', $name);
            })
            ->first();

        return $userPreference?->value ?? $this->getDefaultPreferenceValue($name, $group) ?? $default;
    }

    /**
     * Retrieve the default value for a preference from its configuration.
     *
     * @param string $name
     * @param string $group
     *
     * @return mixed
     */
    private function getDefaultPreferenceValue(string $name, string $group = 'general'): mixed
    {
        return Preference::where('group', $group)->where('name', $name)->first()?->default_value ?? null;
    }

    /**
     * Set a preference value, handling validation and persistence.
     *
     * @param string $name
     * @param mixed  $value
     * @param string $group
     *
     * @throws ValidationException
     * @throws \RuntimeException
     */
    public function setPreference(string $name, mixed $value, string $group = 'general'): void
    {
        /**@var Preference $preference * */
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        if (!$preference) {
            throw new \RuntimeException('Preference not found.');
        }


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
     * @param string $name
     * @param string $group
     *
     * @return int
     */
    public function removePreference(string $name, string $group = 'general'): int
    {
       return $this->userPreferences()->whereHas('preference', function ($query) use ($group, $name) {
            $query->where('group', $group)->where('name', $name);
        })->delete();
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
        $query = $this->userPreferences()->with('preference');

        if ($group) {
            $query->whereHas('preference', function ($query) use ($group) {
                $query->where('group', $group);
            });
        }

        return $query->get();
    }
}
