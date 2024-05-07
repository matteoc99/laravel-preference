<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

use Illuminate\Foundation\Application;
use Matteoc99\LaravelPreference\Factory\PreferenceBuilder;
use Matteoc99\LaravelPreference\Rules\InRule;
use Matteoc99\LaravelPreference\Tests\TestCase;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\General;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Enums\VideoPreferences;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\User;

class ApiTestCase extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();

        PreferenceBuilder::init(General::LANGUAGE)->withRule(new InRule('it', 'en', 'de'))->create();
    }

    /**
     * Define environment setup.
     *
     * @param  Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {

        parent::getEnvironmentSetUp($app);
        $app['config']->set('user_preference.routes.enabled', true);
        $app['config']->set('user_preference.routes.middlewares', [

        ]);
        $app['config']->set('user_preference.routes.prefix', 'preferences');
        $app['config']->set('user_preference.routes.groups', [
            'general' => General::class,
            'video' => VideoPreferences::class,
        ]);
        $app['config']->set('user_preference.routes.scopes', [
            'user' => User::class,
        ]);
    }
}
