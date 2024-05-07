<?php

namespace Matteoc99\LaravelPreference\Factory\builders;

use Matteoc99\LaravelPreference\Exceptions\InvalidStateException;
use Matteoc99\LaravelPreference\Utils\ValidationHelper;

class ObjectPreferenceBuilder extends BaseBuilder
{
    /** @throws InvalidStateException */
    public function setAllowedClasses(...$classes): static
    {
        $this->addState(self::STATE_ALLOWED_VALUES_SET);

        ValidationHelper::validateAllowedClasses($this->preference->cast, $classes);

        $this->preference->allowed_values = $classes;

        return $this;
    }
}
