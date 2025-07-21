<?php

namespace Matteoc99\LaravelPreference\Events;

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Matteoc99\LaravelPreference\Contracts\PreferenceableModel;

abstract class PreferenceEvent
{
    use Dispatchable, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        public readonly PreferenceableModel $model,
        public readonly ?Authenticatable $user
    ) {}
}
