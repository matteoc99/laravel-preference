<?php

namespace Matteoc99\LaravelPreference\Rules;

use Matteoc99\LaravelPreference\Contracts\HasValidation;

abstract class DataRule implements HasValidation
{

    private array $data;

    public function __construct(...$data)
    {
        $this->setData($data);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}