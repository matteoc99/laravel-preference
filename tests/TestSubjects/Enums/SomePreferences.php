<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum SomePreferences: string implements PreferenceGroup
{
    case SOME_LANGUAGE = 'somelanguage';
    case SOME_CONFIG = 'someconfig';
}
