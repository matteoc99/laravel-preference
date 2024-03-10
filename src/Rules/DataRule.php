<?php

namespace Matteoc99\LaravelPreference\Rules;

use Matteoc99\LaravelPreference\Contracts\HasValidation;

abstract class DataRule implements HasValidation
{

    protected array $data;


    public function getData(): array
    {
        return $this->getData();
    }

    public function setData(array $data): void
    {
        $this->data = $data;
    }
}