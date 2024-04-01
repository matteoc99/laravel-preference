<?php

namespace Matteoc99\LaravelPreference\Contracts;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Enums\PolicyAction;
use Matteoc99\LaravelPreference\Exceptions\PreferenceNotFoundException;

interface PreferenceableModel
{
    /**
     * Retrieve all preferences for the current user, optionally filtered by group.
     *
     * @param string|null $group
     *
     * @return Collection
     */
    public function getPreferences(string $group = null): Collection;

    /**
     * Remove a preference for the current user.
     *
     * @param PreferenceGroup $name
     *
     * @return int
     * @throws PreferenceNotFoundException
     */
    public function removePreference(PreferenceGroup $name): int;

    /**
     * Set a preference value, handling validation and persistence.
     *
     * @param PreferenceGroup $name
     * @param mixed           $value
     *
     * @throws ValidationException
     * @throws PreferenceNotFoundException
     */
    public function setPreference(PreferenceGroup $name, mixed $value): void;

    /**
     * Retrieve a preference value, prioritizing user settings, then defaults.
     *
     * @param PreferenceGroup $name
     * @param mixed|null      $default
     *
     * @return mixed
     * @throws PreferenceNotFoundException
     */
    public function getPreference(PreferenceGroup $name, mixed $default = null): mixed;


    /**
     * check if the user is authorized
     *
     * @param Authenticatable|null $user
     * @param PolicyAction         $action
     *
     * @return bool
     */
    public function isUserAuthorized(?Authenticatable $user, PolicyAction $action): bool;

}