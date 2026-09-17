<?php

namespace Propello\PackageLearningS;

use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use Propello\PackageLearningS\Commands\PackageLearningSCommand;

class PackageLearningSServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package
            ->name('package-learning-s')
            ->hasConfigFile()
            ->hasViews()
            ->hasMigration('create_package_learning_s_table')
            ->hasCommand(PackageLearningSCommand::class);
    }
}
