<?php

namespace Matteoc99\LaravelPreference\Tests;

use Matteoc99\LaravelPreference\Console\Commands\PreferenceMigrate;
use Matteoc99\LaravelPreference\Enums\ApplicationVersion;

class PreferenceMigrateCommandTest extends TestCase
{
    /** @test */
    public function artisan_command_migrates_serialize_incompatibilities()
    {

        $this->app->make(PreferenceMigrate::class);

        $this->artisan('preference:migrate:version')
            ->expectsQuestion('From which version?', ApplicationVersion::Version1->value)
            ->expectsQuestion('To which version?', ApplicationVersion::Version2->value)
            ->assertExitCode(0);

    }
}
