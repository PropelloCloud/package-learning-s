<?php

namespace Sorayataraszka\PackageLearningS\Commands;

use Illuminate\Console\Command;

class PackageLearningSCommand extends Command
{
    public $signature = 'package-learning-s';

    public $description = 'My command';

    public function handle(): int
    {
        $this->comment('All done');

        return self::SUCCESS;
    }
}
