<?php

namespace Matteoc99\LaravelPreference\Exceptions;

use Exception;
use Throwable;

class InvalidStateException extends Exception
{

    private int $state;

    public function __construct($state, string $message, ?Throwable $previous = null)
    {
        $this->state = $state;
        parent::__construct($message, 0, $previous);
    }

    /**
     * @return int
     */
    public function getState(): int
    {
        return $this->state;
    }
}