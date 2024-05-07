<?php

namespace Matteoc99\LaravelPreference\Traits;

use Matteoc99\LaravelPreference\Exceptions\InvalidStateException;
use ReflectionClass;

trait HasState
{
    private int $state = 0;

    /** @throws InvalidStateException */
    protected function addState($state)
    {
        if ($this->isStateSet($state)) {
            throw new InvalidStateException($this->getState(), 'The model is already in the state: '.$this->getStateName($state));
        }

        $this->state |= $state;
    }

    public function isStateSet($state): bool
    {
        return ($this->state & $state) === $state;
    }

    /** @throws InvalidStateException */
    protected function removeState($state): void
    {
        if (! $this->isStateSet($state)) {
            throw new InvalidStateException($this->getState(), 'The model is not in the state: '.$this->getStateName($state).' and can not be removed. ');
        }
        $this->state &= ~$state;
    }

    protected function getStateName($state): string
    {
        $reflection = new ReflectionClass($this);
        $constants = $reflection->getConstants();

        foreach ($constants as $name => $value) {
            if ($state === $value) {
                return $name;
            }
        }

        return 'Unknown State';
    }

    public function getState(): int
    {
        return $this->state;
    }
}
