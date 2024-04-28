<?php

namespace Matteoc99\LaravelPreference\Factory\builders;

use Illuminate\Contracts\Validation\ValidationRule;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

abstract class BaseBuilder
{
    protected Preference $preference;

    public function __construct(PreferenceGroup $name, CastableEnum $cast)
    {
        $this->preference = new Preference();

        $this->withName($name)->withCast($cast)->nullable(false);
    }

    private function withCast(CastableEnum $cast): static
    {
        $this->preference->cast = $cast;
        return $this;
    }

    private function withName(PreferenceGroup $name): static
    {
        SerializeHelper::conformNameAndGroup($name, $group);

        $this->preference->name  = $name;
        $this->preference->group = $group;
        return $this;
    }


    public function withPolicy(PreferencePolicy $policy): static
    {
        $this->preference->policy = $policy;
        return $this;
    }

    public function withDefaultValue(mixed $value): static
    {
        $this->preference->default_value = $value;
        return $this;
    }

    public function withDescription(string $description): static
    {
        $this->preference->description = $description;
        return $this;
    }

    public function withRule(ValidationRule $rule): static
    {
        $this->preference->rule = $rule;
        return $this;
    }

    public function nullable(bool $nullable = true)
    {
        $this->preference->nullable = $nullable;
        return $this;
    }


    public function create(): Preference
    {
        return $this->updateOrCreate();
    }

    public function updateOrCreate(): Preference
    {
        ValidationHelper::validatePreference($this->preference);


        $this->preference = Preference::updateOrCreate(
            $this->preference->toArrayOnly(['name', 'group']),
            $this->preference->attributesToArray()
        );
        return $this->preference->fresh();
    }

}