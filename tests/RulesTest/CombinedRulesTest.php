<?php

namespace Matteoc99\LaravelPreference\Tests\RulesTest;

use Matteoc99\LaravelPreference\Enums\Type;
use Matteoc99\LaravelPreference\Rules\AndRule;
use Matteoc99\LaravelPreference\Rules\BetweenRule;
use Matteoc99\LaravelPreference\Rules\InRule;
use Matteoc99\LaravelPreference\Rules\InstanceOfRule;
use Matteoc99\LaravelPreference\Rules\IsRule;
use Matteoc99\LaravelPreference\Rules\LowerThanRule;
use Matteoc99\LaravelPreference\Rules\OrRule;
use Matteoc99\LaravelPreference\Tests\TestCase;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\Theme;

class CombinedRulesTest extends TestCase
{
    public static function orRuleProvider()
    {
        return [
            [new OrRule(new BetweenRule(2.4, 5.5), new LowerThanRule(2)), 3, true, 'Expected OrRule to pass when the first rule passes.'],
            [new OrRule(new BetweenRule(2.4, 5.5), new LowerThanRule(2)), 1, true, 'Expected OrRule to pass when the second rule passes.'],
            [new OrRule(new BetweenRule(2.4, 5.5), new LowerThanRule(2)), 6, false, 'Expected OrRule to fail when neither rule passes.'],
            [new OrRule(new InstanceOfRule(Theme::class), new IsRule(Type::ITERABLE)), Theme::LIGHT, true, 'Expected OrRule to pass with an instance of SomeClass.'],
            [new OrRule(new InstanceOfRule(Theme::class), new IsRule(Type::ITERABLE)), [], true, 'Expected OrRule to pass with an iterable (array).'],
            [new OrRule(new LowerThanRule(5), new BetweenRule(10, 20)), 25, false, 'Expected OrRule to fail when all rules fail.'],
            [new OrRule(new AndRule(new BetweenRule(5, 15), new LowerThanRule(20)), new InRule("it", "en", "de")), 10, true, 'Expected OrRule to pass with nested AndRule conditions met.'],
            [new OrRule(new AndRule(new BetweenRule(5, 15), new LowerThanRule(20)), new InRule("it", "en", "de")), "en", true, 'Expected OrRule to pass with value in InRule parameters.'],
            [new OrRule(new AndRule(new BetweenRule(5, 15), new LowerThanRule(20)), new InRule("it", "en", "de")), "fr", false, 'Expected OrRule to fail when nested conditions are not met.'],
        ];
    }

    public static function andRuleProvider(): array
    {
        return [
            [new AndRule(new BetweenRule(2.4, 5.5), new LowerThanRule(6)), 3, true, 'Expected AndRule to pass when all conditions are met.'],
            [new AndRule(new BetweenRule(2.4, 5.5), new LowerThanRule(3)), 4, false, 'Expected AndRule to fail when one condition fails.'],
            [new AndRule(new BetweenRule(2.4, 5.5), new LowerThanRule(3)), 6, false, 'Expected AndRule to fail when both conditions fail.'],
            [new AndRule(new InstanceOfRule(Theme::class), new IsRule(Type::ITERABLE)), Theme::LIGHT, false, 'Expected AndRule to fail since SomeClass is not iterable.'],
            [new AndRule(new InstanceOfRule(Theme::class), new IsRule(Type::ITERABLE)), [], false, 'Expected AndRule to fail since array is not an instance of SomeClass.'],
            [new AndRule(new OrRule(new BetweenRule(10, 20), new InRule("it", "en", "de")), new LowerThanRule(15)), 12, true, 'Expected AndRule to pass with nested OrRule condition met and value less than 15.'],
            [new AndRule(new BetweenRule(2.4, 5.5), new LowerThanRule(5.5)), 5.4, true, 'Expected AndRule to pass at the edge of the range.'],
        ];
    }

    /**
     * @dataProvider orRuleProvider
     * @dataProvider andRuleProvider
     */
    public function test_or_rule_validation($rule, $value, $expected, $message)
    {
        $failed      = false;
        $failClosure = function ($message) use (&$failed) {
            $failed = true;
        };

        $rule->validate('attribute', $value, $failClosure);
        if ($expected) {
            $this->assertFalse($failed, $message);
        } else {
            $this->assertTrue($failed, $message);
        }
    }


}