<?php

namespace Matteoc99\LaravelPreference\Console\Commands;

use Illuminate\Console\Command;
use Matteoc99\LaravelPreference\Enums\ApplicationVersion;

class PreferenceMigrate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'preference:migrate:version';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate incompatibilities ';

    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $version_from = ApplicationVersion::from($this->choice(
            'From which version?',
            [ApplicationVersion::Version1->value],
            0,
        ));

        $version_to = ApplicationVersion::from($this->choice(
            'To which version?',
            [ApplicationVersion::Version2->value],
            0
        ));

        // handle future cases

    }
}