<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum PlainEnum
{
    case LANGUAGE;
    case QUALITY;
    case CONFIG;
}