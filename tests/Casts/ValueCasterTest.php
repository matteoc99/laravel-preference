<?php

namespace Matteoc99\LaravelPreference\Tests\Casts;

use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Tests\Enums\VideoPreferences;
use Matteoc99\LaravelPreference\Utils\SerializeHelper;

class ValueCasterTest extends CasterTestCase
{

    /** @test */
    public function test_get()
    {
        $caster = new ValueCaster();

        $this->dummyPref->cast = Cast::BOOL;
        $result                = $caster->get($this->dummyPref, '', false, []);
        $this->assertFalse($result);

        // With Int Cast
        $this->dummyPref->cast = Cast::INT;
        $result                = $caster->get($this->dummyPref, '', '123', []);
        $this->assertEquals(123, $result); // Assert integer conversion

        // With Float Cast
        $this->dummyPref->cast = Cast::FLOAT;
        $result                = $caster->get($this->dummyPref, '', '3.14', []);
        $this->assertEquals(3.14, $result); // Assert float conversion

        // With String Cast (or unknown)
        $this->dummyPref->cast = Cast::STRING;
        $result                = $caster->get($this->dummyPref, '', 'hello', []);
        $this->assertEquals('hello', $result); // Assert no change


        $this->dummyPref->cast = Cast::ARRAY;
        $result                = $caster->get($this->dummyPref, '', "[1, \"hello\"]", []);
        $this->assertIsArray($result); // Assert valid JSON representation

        // With Date Cast
        $this->dummyPref->cast = Cast::DATE;
        $result                = $caster->get($this->dummyPref, '', '2023-12-25', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('2023-12-25', $result->toDateString());

        // With Time Cast
        $this->dummyPref->cast = Cast::TIME;
        $result                = $caster->get($this->dummyPref, '', '15:30:00', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('15:30:00', $result->toTimeString());

        // With DateTime Cast
        $this->dummyPref->cast = Cast::DATETIME;
        $result                = $caster->get($this->dummyPref, '', '2023-12-25 15:30:00', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('2023-12-25 15:30:00', $result->toDateTimeString());

        // With Timestamp Cast
        $this->dummyPref->cast = Cast::TIMESTAMP;
        $timestamp             = 1679164665;
        $result                = $caster->get($this->dummyPref, '', (string)$timestamp, []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals($timestamp, $result->getTimestamp());

        $this->dummyPref->cast = Cast::BACKED_ENUM;
        $result                = $caster->get($this->dummyPref, '', SerializeHelper::serializeEnum(VideoPreferences::LANGUAGE), []);
        $this->assertEquals(VideoPreferences::LANGUAGE, $result);
        $this->assertEquals(VideoPreferences::LANGUAGE->name, $result->name);


        $this->dummyPref->cast = null;
        $val                   = 12345;
        $result                = $caster->get($this->dummyPref, '', $val, []);
        $this->assertEquals($val, $result);

    }

    /** @test */
    public function test_set()
    {
        $caster = new ValueCaster();

        // With Bool Cast
        $this->dummyPref->cast = Cast::BOOL;
        $result                = $caster->set($this->dummyPref, '', true, []);
        $this->assertEquals(true, $result);

        // With Array cast
        $this->dummyPref->cast = Cast::ARRAY;
        $result                = $caster->set($this->dummyPref, '', [1, "hello"], []);
        $this->assertJson($result); // Assert valid JSON representation

        $this->dummyPref->cast = Cast::DATE;
        $date                  = \Carbon\Carbon::now();
        $result                = $caster->set($this->dummyPref, '', $date, []);
        $this->assertEquals($date->toDateString(), $result);

        // With Time Cast
        $this->dummyPref->cast = Cast::TIME;
        $time                  = \Carbon\Carbon::parse('10:30:00');
        $result                = $caster->set($this->dummyPref, '', $time, []);
        $this->assertEquals($time->toTimeString(), $result);

        // With Datetime Cast
        $this->dummyPref->cast = Cast::DATETIME;
        $datetime              = \Carbon\Carbon::now();
        $result                = $caster->set($this->dummyPref, '', $datetime, []);
        $this->assertEquals($datetime->toDateTimeString(), $result);

        // With Timestamp Cast
        $this->dummyPref->cast = Cast::TIMESTAMP;
        $timestamp             = time();
        $result                = $caster->set($this->dummyPref, '', $timestamp, []);
        $this->assertEquals((string)$timestamp, $result);

        $this->dummyPref->cast = Cast::BACKED_ENUM;
        $result                = $caster->set($this->dummyPref, '', VideoPreferences::LANGUAGE, []);
        $this->assertEquals(SerializeHelper::serializeEnum(VideoPreferences::LANGUAGE), $result);
        $this->assertEquals(VideoPreferences::LANGUAGE, SerializeHelper::deserializeEnum($result));

        $this->dummyPref->cast = null;
        $val                   = 12345;
        $result                = $caster->set($this->dummyPref, '', $val, []);
        $this->assertEquals($val, $result);
    }

}