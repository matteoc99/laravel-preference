<?php

namespace Matteoc99\LaravelPreference\Tests\RulesTest;

use Carbon\Carbon;
use Matteoc99\LaravelPreference\Enums\Type;
use Matteoc99\LaravelPreference\Rules\InstanceOfRule;
use Matteoc99\LaravelPreference\Rules\IsRule;
use Matteoc99\LaravelPreference\Tests\TestCase;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\PlainEnum;

class RulesTest extends TestCase
{

    public static function valueProvider(): array
    {
        return [
            'bool_true'          => [Type::BOOL, true, true],
            'bool_false'         => [Type::BOOL, false, true],
            'int'                => [Type::INT, 123, true],
            'float'              => [Type::FLOAT, 1.23, true],
            'string'             => [Type::STRING, 'test', true],
            'array'              => [Type::ARRAY, [], true],
            'object'             => [Type::OBJECT, (object)[], true],
            'callable'           => [Type::CALLABLE, function () { }, true],
            'iterable_array'     => [Type::ITERABLE, [], true],
            'iterable_generator' => [Type::ITERABLE, (function () { yield 1; })(), true],
            'null'               => [Type::NULL, null, true],
            'resource'           => [Type::RESOURCE, fopen('php://temp', 'r'), true],
            // Test incorrect Type
            'int_as_string'      => [Type::INT, '123', false],
            'string_as_int'      => [Type::STRING, 123, false],
            'bool_as_int'        => [Type::BOOL, 0, false],
        ];
    }

    public static function instanceValueProvider(): array
    {
        return [
            'enum_true'         => [\UnitEnum::class, PlainEnum::QUALITY, true],
            'enum_false'        => [\UnitEnum::class, Carbon::now(), false],
            'backed_enum_false' => [\BackedEnum::class, PlainEnum::QUALITY, false],
            'backed_enum_true'  => [\BackedEnum::class, General::LANGUAGE, true],
        ];
    }

    /**
     * @dataProvider valueProvider
     */
    public function testIsRuleValidation(Type $type, mixed $value, bool $expectedOutcome)
    {
        $rule = new IsRule($type);

        $failed      = false;
        $failClosure = function ($message) use (&$failed) {
            $failed = true;
        };

        $rule->validate('attribute', $value, $failClosure);

        if ($expectedOutcome) {
            $this->assertFalse($failed, "Expected validation to pass for type: {$type->name}");
        } else {
            $this->assertTrue($failed, "Expected validation to fail for type: {$type->name}");
        }
    }


    /**
     * @dataProvider instanceValueProvider
     */
    public function testInstanceOfRuleValidation(string $class, mixed $value, bool $expectedOutcome)
    {
        $rule = new InstanceOfRule($class);

        $failed      = false;
        $failClosure = function ($message) use (&$failed) {
            $failed = true;
        };

        $rule->validate('attribute', $value, $failClosure);

        if ($expectedOutcome) {
            $this->assertFalse($failed, "Expected validation to pass: $class");
        } else {
            $this->assertTrue($failed, "Expected validation to fail: $class");
        }
    }

}