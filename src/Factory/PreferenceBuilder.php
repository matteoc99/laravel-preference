<?php

namespace Matteoc99\LaravelPreference\Factory;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\PreferenceGroup;
use Matteoc99\LaravelPreference\Contracts\PreferencePolicy;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

class PreferenceBuilder
{
    private Preference $preference;

    private function __construct()
    {
        $this->preference = new Preference();
    }

    public static function init(PreferenceGroup $name, CastableEnum $cast = Cast::STRING): static
    {
        $builder = new PreferenceBuilder();
        return $builder->withName($name)->withCast($cast)->nullable(false);
    }

    public static function delete(PreferenceGroup $name): int
    {
        SerializeHelper::conformNameAndGroup($name, $group);
        $query = Preference::query()->where('name', $name);

        if ($query->count() > 1) {
            $query->where('group', $group);
        }

        return $query->delete();
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

    /**
     * @throws ValidationException
     */
    public static function initBulk(array $preferences, bool $nullable = false): void
    {
        if (empty($preferences)) {
            throw new InvalidArgumentException("no preferences provided");
        }

        foreach ($preferences as $index => &$preferenceData) {
            if (empty($preferenceData['cast'])) {
                $preferenceData['cast'] = Cast::STRING;
            }
            if (!array_key_exists('nullable', $preferenceData)) {
                $preferenceData['nullable'] = $nullable;
            }

            ValidationHelper::validatePreferenceData($preferenceData, $index);

            SerializeHelper::conformNameAndGroup($preferenceData['name'], $preferenceData['group']);

            if (array_key_exists('rule', $preferenceData)) {
                $preferenceData['rule'] = serialize($preferenceData['rule']);
            }
            if (array_key_exists('default_value', $preferenceData)) {
                $valueCaster                     = new ValueCaster($preferenceData['cast']);
                $preferenceData['default_value'] = $valueCaster->set(null, '', $preferenceData['default_value'], []);
            }

            $preferenceData['cast'] = serialize($preferenceData['cast']);

            // Ensure Defaults
            $preferenceData = array_merge([
                'group'         => 'general',
                'default_value' => null,
                'description'   => '',
                'policy'        => null,
                'rule'          => null,
                'nullable'      => false,
            ], $preferenceData);
        }

        Preference::upsert($preferences, ['name', 'group']);
    }

    public static function deleteBulk(array $preferences): int
    {
        if (empty($preferences)) {
            throw new InvalidArgumentException("no preferences provided");
        }
        $query = Preference::query();

        foreach ($preferences as $index => $preferenceData) {
            if (empty($preferenceData['name']) || !($preferenceData['name'] instanceof PreferenceGroup)) {
                throw new InvalidArgumentException(
                    sprintf("index: #%s name is required and must implement PreferenceGroup", $index)
                );
            }

            SerializeHelper::conformNameAndGroup($preferenceData['name'], $preferenceData['group']);

            $query->orWhere(function (Builder $query) use ($preferenceData) {
                $query->where('name', $preferenceData['name']);
                $query->where('group', $preferenceData['group']);
            });
        }

        return $query->delete();
    }


}