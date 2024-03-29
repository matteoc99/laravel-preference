<?php

namespace Matteoc99\LaravelPreference\Tests\Casts;

use Matteoc99\LaravelPreference\Casts\EnumCaster;
use Matteoc99\LaravelPreference\Enums\Cast;

class EnumCasterTest extends CasterTestCase
{

    /** @test */
    public function test_get()
    {
        $caster = new EnumCaster();
        $result = $caster->get($this->dummyPref, '', json_encode(['class' => Cast::class, 'value' => 'int']), []);
        $this->assertEquals(Cast::INT, $result);

        $result = $caster->get($this->dummyPref, '', null, []);
        $this->assertNull($result);

        $this->expectException(\InvalidArgumentException::class);
        $caster->get($this->dummyPref, '', json_encode(['class' => 'Foo\Bar', 'value' => 'int']), []);

        $this->expectException(\InvalidArgumentException::class);
        $caster->get($this->dummyPref, '', json_encode(['class' => Cast::class, 'value' => 'xyz']), []);
    }

    /** @test */
    public function test_set()
    {
        $caster = new EnumCaster();

        $result = $caster->set($this->dummyPref, '', Cast::STRING, []);
        $this->assertEquals(json_encode(['class' => Cast::class, 'value' => 'string']), $result);

        $this->expectException(\InvalidArgumentException::class);
        $caster->set($this->dummyPref, '', 'not an enum', []);
    }

    /** @test */
    public function test_get_with_invalid_json()
    {
        $caster = new EnumCaster();

        $this->expectException(\InvalidArgumentException::class);
        $caster->get($this->dummyPref, '', json_encode(['value' => 'int']), []);

        $this->expectException(\InvalidArgumentException::class);
        $caster->get($this->dummyPref, '', json_encode(['class' => Cast::class, 'value' => 123]), []);
    }
}