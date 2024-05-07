<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;
use Matteoc99\LaravelPreference\Models\Preference;

interface PreferenceableModel
{
    /** Retrieve all preferences for the current user, optionally filtered by group. */
    public function getPreferences(?string $group = null): Collection;

    /**
     * Remove a preference for the current user.
     *
     *
     * @throws PreferenceNotFoundException
     */
    public function removePreference(PreferenceGroup $name): int;

    /**
     * Reset the model to its original state
     * Remove all user's preferences.
     *
     *
     * @return int Number of deleted records.
     *
     * @throws AuthorizationException
     */
    public function removeAllPreferences(): int;

    /**
     * Set a preference value, handling validation and persistence.
     *
     *
     * @throws ValidationException
     * @throws PreferenceNotFoundException
     */
    public function setPreference(PreferenceGroup $name, mixed $value): void;

    /**
     * Retrieve a preference value, prioritizing user settings, then defaults.
     *
     *
     * @throws PreferenceNotFoundException
     */
    public function getPreference(PreferenceGroup $name, mixed $default = null): mixed;

    /**
     * Get a user's preference value or default if not set, transformed for data transfer
     *
     * @param  string|null  $default  Default value if preference not set.
     *
     * @throws AuthorizationException
     * @throws PreferenceNotFoundException
     */
    public function getPreferenceDto(PreferenceGroup|Preference $preference, mixed $default = null): array;

    /** check if the user is authorized */
    public function isUserAuthorized(?Authenticatable $user, PolicyAction $action): bool;
}
