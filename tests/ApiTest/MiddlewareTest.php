<?php

namespace Matteoc99\LaravelPreference\Tests\ApiTest;

use Illuminate\Foundation\Application;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Middlewares\Dummy2Middleware;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Middlewares\DummyMiddleware;

class MiddlewareTest extends ApiTestCase
{
    /**
     * Define environment setup.
     *
     * @param Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app): void
    {
        parent::getEnvironmentSetUp($app);

        $app['config']->set('user_preference.routes.middlewares', [
            'user'         => DummyMiddleware::class,
            'user.general' => Dummy2Middleware::class,
        ]);
    }

    /** @test */
    public function it_applies_auth_middleware_to_all_routes()
    {
        $response = $this->get('preferences/user/1/general');
        $response->assertHeader('X-Dummy-Middleware', 'Applied');

        $response = $this->get('/preferences/user/1/video');
        $response->assertHeader('X-Dummy-Middleware', 'Applied');
    }

    /** @test */
    public function it_conditionally_applies_verified_middleware()
    {
        $response = $this->get('/preferences/user/1/general');
        $response->assertHeader('X-Dummy2-Middleware', 'Applied');

        $response = $this->get('/preferences/user/1/video');
        $response->assertHeaderMissing('X-Dummy2-Middleware');
    }

    /** @test */
    public function it_handles_non_existent_routes_gracefully()
    {
        $response = $this->get('/preferences/user/1/nonexistent');
        $response->assertStatus(404);

        $response = $this->get('/preferences/user/stringId/general');
        $response->assertStatus(404);

        $response = $this->get('/preferences/user/1/general/Invalid%Preference');
        $response->assertStatus(404);
    }

    /** @test */
    public function it_applies_middleware_to_all_http_methods()
    {
        $postResponse = $this->patch('/preferences/user/1/general/language', ['value' => 'de']);
        $postResponse->assertHeader('X-Dummy2-Middleware', 'Applied');

        $deleteResponse = $this->delete('/preferences/user/1/general/language');
        $deleteResponse->assertHeader('X-Dummy2-Middleware', 'Applied');
    }

}