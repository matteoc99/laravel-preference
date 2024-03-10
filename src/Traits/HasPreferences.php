<?php

namespace Matteoc99\LaravelPreference\Traits;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Models\UserPreference;

trait HasPreferences
{
    public function userPreferences()
    {
        return $this->morphMany(UserPreference::class, 'preferenceable');
    }

    public function getPreference(string $name, string $group = 'general', mixed $default = null): mixed
    {
        $userPreference = $this->userPreferences()
            ->where('group', $group)
            ->where('name', $name)
            ->first();

        return $userPreference?->value ?? $default ?? $this->getDefaultPreferenceValue($name, $group);
    }

    private function getDefaultPreferenceValue(string $name, string $group = 'general'): mixed
    {
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        return $preference ? $preference->default_value : null;
    }

    public function setPreference(string $name, mixed $value, string $group = 'general'): self
    {
        /**@var Preference $preference * */
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        if (!$preference) {
            throw new \RuntimeException('Preference not found.');
        }

        $rules = [$preference->cast->validation(), $preference->rule];

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw new ValidationException($validator);
        }

        $this->userPreferences()->updateOrCreate(
            ['preference_id'=>$preference->id],
            ['value' => $value]
        );

        return $this;
    }

    public function removePreference(string $name, string $group = 'general'): self
    {
        $this->userPreferences()
            ->where('name', $name)
            ->where('group', $group)
            ->delete();

        return $this;
    }

    public function getPreferences(string $group = 'general'): Collection
    {
        $query = $this->userPreferences();

        if ($group !== null) {
            $query->where('group', $group);
        }

        return $query->get();
    }
}