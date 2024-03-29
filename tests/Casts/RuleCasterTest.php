<?php

namespace Matteoc99\LaravelPreference\Tests\Casts;

use Matteoc99\LaravelPreference\Casts\RuleCaster;
use Matteoc99\LaravelPreference\Tests\Models\LowerThanRule;

class RuleCasterTest extends CasterTestCase
{

    /** @test */
    public function test_get()
    {
        $caster = new RuleCaster();

        $result = $caster->get($this->dummyPref, '', json_encode(['class' => LowerThanRule::class, 'data' => [10]]), []);
        $this->assertInstanceOf(LowerThanRule::class, $result);
        $this->assertEquals([10], $result->getData());

        $result = $caster->get($this->dummyPref, '', json_encode(['class' => LowerThanRule::class, 'data' => [50]]), []);
        $this->assertTrue($result->passes('test', 40));
        $this->assertFalse($result->passes('test', 60));
    }

    /** @test */
    public function test_set()
    {
        $caster = new RuleCaster();

        $rule   = new LowerThanRule(20);
        $result = $caster->set($this->dummyPref, '', $rule, []);
        $this->assertEquals(json_encode(['class' => LowerThanRule::class, 'data' => [20]]), $result);

        $this->expectException(\InvalidArgumentException::class);
        $caster->set($this->dummyPref, '', new \stdClass(), []);
    }
}