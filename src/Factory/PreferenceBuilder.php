<?php

namespace Matteoc99\LaravelPreference\Factory;

use Illuminate\Database\Eloquent\Builder;
use Matteoc99\LaravelPreference\Contracts\CastableEnum;
use Matteoc99\LaravelPreference\Contracts\HasValidation;
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
        $builder = new PreferenceBuilder();
        return $builder->withName($name)->withCast($cast)->withGroup('general');
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

    public function withRule(HasValidation $rule): static
    {
        $this->preference->rule = $rule;
        return $this;
    }

    public function create(): Preference
    {
        $this->preference->save();
        return $this->preference;
    }

    public function updateOrCreate(): Preference
    {
        $this->preference = Preference::updateOrCreate($this->preference->toArrayOnly(['name', 'group']));
        return $this->preference;
    }

    public function delete(): int
    {
        $query = Preference::query()->where('name', $this->preference->name);

        if ($query->count() > 1) {
            $query->where('group', $this->preference->group);
        }

        return $query->delete();
    }

    public static function initBulk(array $preferences)
    {
        if (empty($preferences)) {
            throw new \InvalidArgumentException("no preferences provided");
        }

        foreach ($preferences as $key => &$preferenceData) {
            if (empty($preferenceData['name'])) {
                throw new \InvalidArgumentException(
                    sprintf("index: #%s name is required", $key)
                );
            }

            if (!($preferenceData['cast'] instanceof CastableEnum)) {
                throw new \InvalidArgumentException(
                    sprintf("index: #%s cast is required and needs to implement 'CastableEnum'", $key)
                );
            }

            // Ensure Defaults
            $preferenceData = array_merge([
                'group'         => 'general',
                'default_value' => null,
                'description'   => '',
                'rule'          => null,
            ], $preferenceData);

            if ($preferenceData['default_value'] && $preferenceData['rule'] && !$preferenceData['rule']->passes('', $preferenceData['default_value'])) {
                throw new \InvalidArgumentException(
                    sprintf("index: #%s default_value fails the validation rule", $key)
                );
            }

        }

        Preference::upsert($preferences, ['name', 'group']);
    }

    public static function deleteBulk(array $preferences): int
    {
        if (empty($preferences)) {
            throw new \InvalidArgumentException("no preferences provided");
        }
        $query = Preference::query();

        foreach ($preferences as $key => $preferenceData) {
            if (empty($preferenceData['name'])) {
                throw new \InvalidArgumentException(
                    sprintf("index: #%s name is required", $key)
                );
            }
            $query->orWhere(function (Builder $query) use ($preferenceData) {
                $query->where('name', $preferenceData['name']);
                $query->where('group', $preferenceData['group'] ?? 'general');
            });
        }

        return $query->delete();
    }

}