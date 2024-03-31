<?php

namespace Matteoc99\LaravelPreference\Exceptions;

use Exception;
use Throwable;

class PreferenceNotFoundException extends Exception
{


    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 0, $previous);
    }
}