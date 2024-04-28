<?php

namespace Matteoc99\LaravelPreference\Factory\builders;

use Matteoc99\LaravelPreference\Utils\ValidationHelper;

class ObjectPreferenceBuilder extends BaseBuilder
{


    public function setAllowedClasses(...$classes): static
    {

        ValidationHelper::validateAllowedClasses($this->preference->cast, $classes);

        $this->preference->allowed_values = $classes;
        return $this;
    }

}