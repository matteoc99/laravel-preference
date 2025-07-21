<?php

namespace Matteoc99\LaravelPreference\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

class PreferenceUpdatedEvent extends PreferenceEvent
{
    /**
     * Create a new event instance.
     */
    public function __construct(
        PreferenceableModel $model,
        ?Authenticatable $user,
        public readonly PreferenceGroup $preferenceGroup,
        public readonly mixed $oldValue,
        public readonly mixed $newValue
    ) {
        parent::__construct($model, $user);
    }
}
