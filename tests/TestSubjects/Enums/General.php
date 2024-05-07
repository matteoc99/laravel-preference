<?php

namespace Matteoc99\LaravelPreference\Tests\TestSubjects\Enums;

use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;

enum General: string implements PreferenceGroup
{
    case LANGUAGE = 'language';
    case QUALITY = 'quality';
    case THEME = 'theme';
    case CONFIG = 'config';
    case VOLUME = 'volume';
    case EMAILS = 'emails';
    case BIRTHDAY = 'birthday';
    case TIMEZONE = 'timezone';
    case OPTIONS = 'options';
    case REMINDER = 'reminder';
}
