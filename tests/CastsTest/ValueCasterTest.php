<?php

namespace Matteoc99\LaravelPreference\Tests\CastsTest;

use Carbon\Carbon;
use Matteoc99\LaravelPreference\Casts\ValueCaster;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Enums\Type;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences;

class ValueCasterTest extends CasterTestCase
{

    /** @test */
    public function test_get()
    {
        $caster = new ValueCaster();

        $this->dummyPref->cast = Cast::DATE;
        $result                = $caster->get($this->dummyPref, '', '2023-12-25', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('2023-12-25', $result->toDateString());

        $this->dummyPref->cast = Cast::TIME;
        $result                = $caster->get($this->dummyPref, '', '15:30:00', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('15:30:00', $result->toTimeString());

        $this->dummyPref->cast = Cast::DATETIME;
        $result                = $caster->get($this->dummyPref, '', '2023-12-25 15:30:00', []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals('2023-12-25 15:30:00', $result->toDateTimeString());

        $this->dummyPref->cast = Cast::TIMESTAMP;
        $timestamp             = 1679164665;
        $result                = $caster->get($this->dummyPref, '', (string)$timestamp, []);
        $this->assertInstanceOf(\Carbon\Carbon::class, $result);
        $this->assertEquals($timestamp, $result->getTimestamp());

        $this->dummyPref->cast = Cast::BACKED_ENUM;
        $result                = $caster->get($this->dummyPref, '', serialize(VideoPreferences::LANGUAGE), []);
        $this->assertEquals(VideoPreferences::LANGUAGE, $result);
        $this->assertEquals(VideoPreferences::LANGUAGE->name, $result->name);

        $this->dummyPref->cast = Cast::ENUM;
        $result                = $caster->get($this->dummyPref, '', serialize(Type::INT), []);
        $this->assertEquals(Type::INT, $result);
        $this->assertEquals(Type::INT->name, $result->name);

        $this->dummyPref->cast = Cast::OBJECT;
        $result                = $caster->get($this->dummyPref, '', serialize($this->testUser), []);
        $this->assertEquals($this->testUser, $result);
        $this->assertEquals($this->testUser->email, $result->email);

        $this->dummyPref->cast = null;
        $val                   = 12345;
        $result                = $caster->get($this->dummyPref, '', $val, []);
        $this->assertEquals($val, $result);


        $this->dummyPref->cast = Cast::NONE;
        $val                   = Carbon::now();
        $result                = $caster->get($this->dummyPref, '', serialize($val), []);
        $this->assertEquals($val, $result);
        $this->assertInstanceOf($val::class, $result);

    }

    /** @test */
    public function test_set()
    {
        $caster = new ValueCaster();

        $this->dummyPref->cast = Cast::DATE;
        $date                  = \Carbon\Carbon::now();
        $result                = $caster->set($this->dummyPref, '', $date, []);
        $this->assertEquals($date->toDateString(), $result);
        $result = $caster->set($this->dummyPref, '', $date->toDateString(), []);
        $this->assertEquals($date->toDateString(), $result);

        $this->dummyPref->cast = Cast::TIME;
        $time                  = \Carbon\Carbon::parse('10:30:00');
        $result                = $caster->set($this->dummyPref, '', $time, []);
        $this->assertEquals($time->toTimeString(), $result);

        $result = $caster->set($this->dummyPref, '', $time->toTimeString(), []);
        $this->assertEquals($time->toTimeString(), $result);

        $this->dummyPref->cast = Cast::DATETIME;
        $datetime              = \Carbon\Carbon::now();
        $result                = $caster->set($this->dummyPref, '', $datetime, []);
        $this->assertEquals($datetime->toDateTimeString(), $result);
        $result = $caster->set($this->dummyPref, '', $datetime->toDateTimeString(), []);
        $this->assertEquals($datetime->toDateTimeString(), $result);

        $this->dummyPref->cast = Cast::TIMESTAMP;
        $timestamp             = time();
        $result                = $caster->set($this->dummyPref, '', $timestamp, []);
        $this->assertEquals((string)$timestamp, $result);

        $this->dummyPref->cast = Cast::BACKED_ENUM;
        $result                = $caster->set($this->dummyPref, '', VideoPreferences::LANGUAGE, []);
        $this->assertEquals(serialize(VideoPreferences::LANGUAGE), $result);
        $this->assertEquals(VideoPreferences::LANGUAGE, unserialize($result));

        $this->dummyPref->cast = Cast::ENUM;
        $result                = $caster->set($this->dummyPref, '', Type::STRING, []);
        $this->assertEquals(serialize(Type::STRING), $result);
        $this->assertEquals(Type::STRING, unserialize($result));


        $this->dummyPref->cast = Cast::OBJECT;
        $result                = $caster->set($this->dummyPref, '', $this->testUser, []);
        $this->assertEquals(serialize($this->testUser), $result);
        $this->assertEquals($this->testUser, unserialize($result));


        $this->dummyPref->cast = null;
        $val                   = 12345;
        $result                = $caster->set($this->dummyPref, '', $val, []);
        $this->assertEquals($val, $result);


        $this->dummyPref->cast = Cast::NONE;
        $val                   = Carbon::now();
        $result                = $caster->set($this->dummyPref, '', $val, []);
        $this->assertEquals($val, unserialize($result));
        $this->assertInstanceOf($val::class, unserialize($result));
    }

    public static function castProvider(): array
    {
        return [
            'bool_false' => [
                Cast::BOOL, false, false, 'assertFalse'
            ],
            'int_string' => [
                Cast::INT, '123', 123, 'assertEquals'
            ],
            'int'        => [
                Cast::INT, 2, 2, 'assertEquals'
            ],
            'int_null'   => [
                Cast::INT, null, null, 'assertEquals'
            ],
            'float'      => [
                Cast::FLOAT, '3.14', 3.14, 'assertEquals'
            ],
            'string'     => [
                Cast::STRING, 'hello', 'hello', 'assertEquals'
            ],
            'array'      => [
                Cast::ARRAY, json_encode([1, "hello"]), [1, "hello"], 'assertEquals'
            ],
            'null'       => [
                null, null, null, 'assertEquals'
            ],
            'none'       => [
                Cast::NONE, null, null, 'assertEquals'
            ],
            'date_null'       => [
                Cast::DATE, null, null, 'assertEquals'
            ],
        ];
    }

    /**
     * @dataProvider castProvider
     */
    public function test_get_with_cast_basic($cast, $value, $expectedResult, $assertionMethod)
    {
        $caster = new ValueCaster();

        $this->dummyPref->cast = $cast;
        $result                = $caster->get($this->dummyPref, '', $value, []);

        $this->$assertionMethod($expectedResult, $result);
    }

    /**
     * @dataProvider castProvider
     */
    public function test_set_with_cast_basic($cast, $expectedValue, $originalValue, $assertionMethod)
    {
        $caster = new ValueCaster();

        $this->dummyPref->cast = $cast;
        $result                = $caster->set($this->dummyPref, '', $originalValue, []);

        $this->$assertionMethod($expectedValue, $result);
    }
}