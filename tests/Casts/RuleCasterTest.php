<?php

namespace Matteoc99\LaravelPreference\Tests\Casts;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Matteoc99\LaravelPreference\Casts\RuleCaster;
use Matteoc99\LaravelPreference\Tests\Models\LowerThanRule;

class RuleCasterTest extends CasterTestCase
{
    use WithFaker, RefreshDatabase;

    /** @test */
    public function test_get()
    {
        $caster = new RuleCaster();

        // Successful deserialization
        $result = $caster->get($this->dummyPref, '', json_encode(['class' => LowerThanRule::class, 'data' => [10]]), []);
        $this->assertInstanceOf(LowerThanRule::class, $result);
        $this->assertEquals([10], $result->getData());

        // DataRule Handling with various data
        $result = $caster->get($this->dummyPref, '', json_encode(['class' => LowerThanRule::class, 'data' => [50]]), []);
        $this->assertTrue($result->passes('test', 40));
        $this->assertFalse($result->passes('test', 60));
    }

    /** @test */
    public function test_set()
    {
        $caster = new RuleCaster();

        // Successful serialization
        $rule   = new LowerThanRule(20);
        $result = $caster->set($this->dummyPref, '', $rule, []);
        $this->assertEquals(json_encode(['class' => LowerThanRule::class, 'data' => [20]]), $result);

        // Edge Case: Non-HasValidation input
        $this->expectException(\InvalidArgumentException::class);
        $caster->set($this->dummyPref, '', new \stdClass(), []); // Not a HasValidation object
    }
}