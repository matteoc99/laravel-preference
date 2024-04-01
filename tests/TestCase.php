<?php

namespace Matteoc99\LaravelPreference\Tests;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Matteoc99\LaravelPreference\PreferenceServiceProvider;
use Matteoc99\LaravelPreference\Tests\TestSubjects\Models\User;

class TestCase extends \Orchestra\Testbench\TestCase
{
    use RefreshDatabase;

    protected User $testUser;
    protected User $otherUser;

    public function setUp(): void
    {
        parent::setUp();

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations');
        $this->artisan('migrate', ['--database' => 'testbench'])->run();

        $this->setUpDatabase($this->app);


        $this->otherUser = new User([
            'email' => 'other@test.com'
        ]);

        $this->testUser = new User([
            'email' => 'test@test.com'
        ]);
        $this->testUser->save();
        $this->otherUser->save();

        Auth::login($this->testUser);
    }

    /**
     * add the package provider
     *
     * @param $app
     *
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [PreferenceServiceProvider::class];
    }

    /**
     * Define environment setup.
     *
     * @param \Illuminate\Foundation\Application $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);
    }

    protected function setUpDatabase($app)
    {
        $schema = $app['db']->connection()->getSchemaBuilder();

        $schema->create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('email');
            $table->softDeletes();
            $table->timestamps();
        });
    }
}