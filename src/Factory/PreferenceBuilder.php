<?php

namespace Matteoc99\LaravelPreference\Factory;

use Illuminate\Validation\Rule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Models\Preference;

class PreferenceBuilder
{
    private Preference $preference;

    private function __construct()
    {
        $this->preference = new Preference();
    }

    public static function init(string $name, CastableEnum $cast = Cast::STRING): static
    {
        $builder = new static();
        return $builder->withName($name)->withCast($cast);
    }

    private function withCast(CastableEnum $cast): static
    {
        $this->preference->cast = $cast;
        return $this;
    }

    private function withName(string $name): static
    {
        $this->preference->name = $name;
        return $this;
    }

    public function withGroup(string $group): static
    {
        $this->preference->group = $group;
        return $this;
    }

    public function withDescription(string $description): static
    {
        $this->preference->description = $description;
        return $this;
    }

    public function withRule(Rule $rule): static
    {
        $this->preference->rule = $rule;
        return $this;
    }

    public function create(): Preference
    {
        $this->preference->save();
        return $this->preference;
    }
}