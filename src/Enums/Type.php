<?php

namespace Matteoc99\LaravelPreference\Enums;

enum Type
{

    case BOOL;
    case INT;
    case FLOAT;
    case STRING;
    case ARRAY;
    case OBJECT;
    case CALLABLE;
    case ITERABLE;
    case NULL;
    case RESOURCE;

}