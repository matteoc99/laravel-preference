<?php

namespace Matteoc99\LaravelPreference\Tests\BuilderTest;

use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Exceptions\InvalidStateException;
use Matteoc99\LaravelPreference\Factory\builders\BaseBuilder;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Models\Preference;
use Matteoc99\LaravelPreference\Rules\InRule;
use Matteoc99\LaravelPreference\Tests\TestCase;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;

class BuilderStateTest extends TestCase
{
    /** @test */
    public function it_can_add_initial_state()
    {
        $builder = PreferenceBuilder::init(General::LANGUAGE);
        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_INITIALIZED));

        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_NAME_SET));
        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_CAST_SET));
    }

    /** @test */
    public function it_throws_exception_when_adding_same_state_twice()
    {
        $this->expectException(InvalidStateException::class);

        try {

            PreferenceBuilder::init(General::LANGUAGE)->withDefaultValue('de')->withDefaultValue('en');
        } catch (InvalidStateException $e) {
            $this->assertEquals(BaseBuilder::STATE_INITIALIZED
                + BaseBuilder::STATE_NAME_SET
                + BaseBuilder::STATE_CAST_SET
                + BaseBuilder::STATE_DEFAULT_SET, $e->getState());

            throw $e;
        }
    }

    /** @test */
    public function it_properly_adds_name_and_cast_state()
    {
        $builder = PreferenceBuilder::init(General::LANGUAGE);
        $builder->withDefaultValue('en');
        $builder->withRule(new InRule('en', 'it', 'de'));

        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_DEFAULT_SET));
        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_RULE_SET));
    }

    /** @test */
    public function it_handles_nullable_state_correctly()
    {
        $builder = PreferenceBuilder::init(General::LANGUAGE);
        $builder->nullable();

        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_NULLABLE_SET));
    }

    /** @test */
    public function it_allows_adding_description_after_default_value()
    {
        $builder = PreferenceBuilder::init(General::LANGUAGE);
        $builder->withDefaultValue('en')->withDescription('Default language setting');

        $this->assertTrue($builder->isStateSet(BaseBuilder::STATE_DESCRIPTION_SET));
    }

    /** @test */
    public function it_creates_preference_properly_with_all_required_states()
    {
        $builder = PreferenceBuilder::init(General::LANGUAGE)
            ->withDefaultValue('en')
            ->withRule(new InRule('en', 'it', 'de'))
            ->withDescription('Language settings');

        $preference = $builder->updateOrCreate();
        $this->assertInstanceOf(Preference::class, $preference);
    }

    /** @test */
    public function init_bulk_successfully_creates_preferences_with_mixed_input_types()
    {
        $preferences = [
            ['name' => General::CONFIG, 'cast' => Cast::ARRAY],
            PreferenceBuilder::init(General::LANGUAGE)->withRule(new InRule('en', 'it', 'de')),
            PreferenceBuilder::init(General::THEME)->withRule(new InRule('light', 'dark')),
        ];

        PreferenceBuilder::initBulk($preferences);

        $this->assertDatabaseCount((new Preference())->getTable(), 3);
        $this->assertDatabaseHas((new Preference())->getTable(), ['name' => General::LANGUAGE]);

        PreferenceBuilder::deleteBulk($preferences);
        $this->assertDatabaseCount((new Preference())->getTable(), 0);

    }
}
