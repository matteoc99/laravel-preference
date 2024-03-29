<?php

namespace Matteoc99\LaravelPreference\Rules;

use Illuminate\Contracts\Validation\ValidationRule;

abstract class DataRule implements ValidationRule
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