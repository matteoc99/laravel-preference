<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Auth;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Policies\MyPolicy;

class PolicyTest extends TestCase
{

    private MyPolicy $policy;

    public function setUp(): void
    {
        parent::setUp();

        $this->policy = new MyPolicy();
        PreferenceBuilder::init(General::LANGUAGE)->withPolicy($this->policy)->nullable()->create();
    }

    /** @test */
    public function no_user_fails_auth()
    {
        Auth::logout();
        $this->expectException(AuthorizationException::class);
        $this->testUser->setPreference(General::LANGUAGE, "it");
    }

    /** @test */
    public function test_user_can_set_and_get_preference()
    {
        Auth::login($this->testUser);
        $this->testUser->setPreference(General::LANGUAGE, "it");

        $this->assertEquals('it', $this->testUser->getPreference(General::LANGUAGE));
    }

    /** @test */
    public function test_user_can_not_set_and_get_admin()
    {
        Auth::login($this->testUser);
        $this->expectException(AuthorizationException::class);
        $this->adminUser->setPreference(General::LANGUAGE, "it");
    }

    /** @test */
    public function noone_can_delete()
    {
        $this->expectException(AuthorizationException::class);
        $this->adminUser->removePreference(General::LANGUAGE,);
    }

}