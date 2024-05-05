<?php

namespace Matteoc99\LaravelPreference\Factory\builders;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\ValidationException;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Exceptions\InvalidStateException;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Traits\HasState;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

abstract class BaseBuilder
{
    use HasState;

    protected Preference $preference;

    const STATE_INITIALIZED        = 1;
    const STATE_CREATED            = 2;
    const STATE_DELETED            = 4;
    const STATE_NAME_SET           = 8;
    const STATE_CAST_SET           = 16;
    const STATE_POLICY_SET         = 32;
    const STATE_RULE_SET           = 64;
    const STATE_DEFAULT_SET        = 128;
    const STATE_DESCRIPTION_SET    = 256;
    const STATE_NULLABLE_SET       = 512;
    const STATE_ALLOWED_VALUES_SET = 1024;

    /**
     * @throws InvalidStateException
     */
    public function __construct(PreferenceGroup $name, CastableEnum $cast)
    {
        $this->preference = new Preference();

        $this->addState(self::STATE_INITIALIZED);

        $this->withName($name)->withCast($cast);
    }

    /**
     * @throws InvalidStateException
     */
    private function withCast(CastableEnum $cast): static
    {
        $this->addState(self::STATE_CAST_SET);
        $this->preference->cast = $cast;
        return $this;
    }

    /**
     * @throws InvalidStateException
     */
    private function withName(PreferenceGroup $name): static
    {
        $this->addState(self::STATE_NAME_SET);

        SerializeHelper::conformNameAndGroup($name, $group);

        $this->preference->name  = $name;
        $this->preference->group = $group;
        return $this;
    }


    /**
     * @throws InvalidStateException
     */
    public function withPolicy(PreferencePolicy $policy): static
    {
        $this->addState(self::STATE_POLICY_SET);
        $this->preference->policy = $policy;
        return $this;
    }

    /**
     * @throws InvalidStateException
     */
    public function withDefaultValue(mixed $value): static
    {
        $this->addState(self::STATE_DEFAULT_SET);

        $this->preference->default_value = $value;
        return $this;
    }

    /**
     * @throws InvalidStateException
     */
    public function withDescription(string $description): static
    {
        $this->addState(self::STATE_DESCRIPTION_SET);

        $this->preference->description = $description;
        return $this;
    }

    /**
     * @throws InvalidStateException
     */
    public function withRule(ValidationRule $rule): static
    {
        $this->addState(self::STATE_RULE_SET);

        $this->preference->rule = $rule;
        return $this;
    }

    /**
     * @throws InvalidStateException
     */
    public function nullable(bool $nullable = true)
    {
        $this->addState(self::STATE_NULLABLE_SET);

        $this->preference->nullable = $nullable;
        return $this;
    }


    /**
     * @deprecated no reason to use this over updateOrCreate, will be removed in v3.x
     */
    public function create(): Preference
    {
        return $this->updateOrCreate();

    }

    /**
     * @throws InvalidStateException
     */
    public function delete(): int
    {

        if (!$this->isStateSet(self::STATE_INITIALIZED)
            || !$this->isStateSet(self::STATE_NAME_SET)) {
            throw new InvalidStateException($this->getState(), "Initialize the builder before deleting the preference");
        }

        $this->addState(self::STATE_DELETED);



        return Preference::query()
            ->where('group', $this->preference->group)
            ->where('name', $this->preference->name)
            ->delete();

    }

    /**
     * @throws InvalidStateException
     * @throws ValidationException
     */
    public function updateOrCreate(): Preference
    {
        $this->addState(self::STATE_CREATED);

        ValidationHelper::validatePreference($this->preference);


        $this->preference = Preference::updateOrCreate(
            $this->preference->toArrayOnly(['name', 'group']),
            $this->preference->attributesToArray()
        );
        return $this->preference->fresh();
    }

}