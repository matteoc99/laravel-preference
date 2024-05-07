<?php

namespace Matteoc99\LaravelPreference\Enums;

enum PolicyAction
{
    case DELETE_ALL;
    case INDEX;
    case GET;
    case UPDATE;
    case DELETE;
}
