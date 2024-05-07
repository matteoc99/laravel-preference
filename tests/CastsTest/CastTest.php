<?php

namespace Matteoc99\LaravelPreference\Tests\CastsTest;

use Illuminate\Support\Facades\Validator;
use Matteoc99\LaravelPreference\Enums\Cast;
use Matteoc99\LaravelPreference\Tests\TestCase;

class CastTest extends TestCase
{
    /** @test */
    public function it_validates_correct_int_values()
    {
        $cast = Cast::INT;

        $this->assertTrue(Validator::make(['value' => 123], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => '123'], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => 12.34], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_float_values()
    {
        $cast = Cast::FLOAT;

        $this->assertTrue(Validator::make(['value' => 12.34], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => '12.34'], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_string_values()
    {
        $cast = Cast::STRING;

        $this->assertTrue(Validator::make(['value' => 'hello world'], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => '12345'], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => ''], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 123], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => true], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_bool_values()
    {
        $cast = Cast::BOOL;

        $this->assertTrue(Validator::make(['value' => true], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => false], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => 0], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => 123], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_array_values()
    {
        $cast = Cast::ARRAY;

        $this->assertTrue(Validator::make(['value' => [1, 2, 3]], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => ['key' => 'value']], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => []], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 123], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_date_values()
    {
        $cast = Cast::DATE;

        $this->assertTrue(Validator::make(['value' => '2023-03-20'], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => '12:30'], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_datetime_values()
    {
        $cast = Cast::DATETIME;

        $this->assertTrue(Validator::make(['value' => '2023-03-20 13:45:00'], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => '12:30'], ['value' => $cast->validation()])->passes());
    }

    /** @test */
    public function it_validates_correct_timestamp_values()
    {
        $cast = Cast::TIMESTAMP;

        $this->assertTrue(Validator::make(['value' => time()], ['value' => $cast->validation()])->passes());
        $this->assertTrue(Validator::make(['value' => 1679190300], ['value' => $cast->validation()])->passes());

        $this->assertFalse(Validator::make(['value' => 'hello'], ['value' => $cast->validation()])->passes());
        $this->assertFalse(Validator::make(['value' => '2023-03-20 13:45:00'], ['value' => $cast->validation()])->passes());
    }
}
