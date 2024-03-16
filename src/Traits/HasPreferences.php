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

    public function getPreference(string $name, string $group = 'general', mixed $default = null): mixed
    {
        $userPreference = $this->userPreferences()
            ->with(['preference' => function ($query) use ($group, $name) {
                $query->where('group', $group)->where('name', $name);
            }])
            ->first();

        if ($userPreference && isset($userPreference->preference)) {
            return $userPreference->preference->value;
        }

        return $default ?? $this->getDefaultPreferenceValue($name, $group);
    }

    private function getDefaultPreferenceValue(string $name, string $group = 'general'): mixed
    {
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        return $preference?->default_value ?? null;
    }

    public function setPreference(string $name, mixed $value, string $group = 'general'): void
    {
        $preference = Preference::where('group', $group)->where('name', $name)->first();

        if (!$preference) {
            throw new \RuntimeException('Preference not found.');
        }

        $rules = [$preference->cast->validation(), $preference->rule ?? []];

        $validator = Validator::make(['value' => $value], ['value' => $rules]);

        if ($validator->fails()) {
            throw new ValidationException($validator,);
        }

        $userPreference = $this->userPreferences()->where('preference_id', $preference->id)->first();

        if (!$userPreference) {
            $this->userPreferences()->create([
                'preference_id' => $preference->id,
                'value'         => $value
            ]);
        } else {
            $userPreference->update(['value' => $value]);
        }
    }

    public function removePreference(string $name, string $group = 'general'): void
    {
        $this->userPreferences()
            ->whereHas('preference', function ($query) use ($group, $name) {
                $query->where('group', $group)->where('name', $name);
            })
            ->delete();
    }

    public function getPreferences(string $group = null): Collection
    {
        $query = $this->userPreferences();

        if ($group !== null) {
            $query->whereHas('preference', function ($query) use ($group) {
                $query->where('group', $group);
            });
        }

        return $query->get();
    }
}
