<?php

namespace Propello\PackageLearningS;

use Propello\PackageLearningS\Listeners\ModelEventListener;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class AuditServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('package-learning-s')
            ->hasConfigFile('multi-audit-log')
            ->hasMigration('create_multi_audit_log_entries_table');
    }

    public function registeringPackage(): void
    {
        $this->app->singleton(AuditManager::class, function () {
            return new AuditManager(config('multi-audit-log.groups', []));
        });
    }

    public function bootingPackage(): void
    {
        $manager  = $this->app->make(AuditManager::class);
        $listener = $this->app->make(ModelEventListener::class);

        foreach (config('multi-audit-log.groups', []) as $groupConfig) {
            foreach ($groupConfig['models'] as $key => $value) {
                $modelClass = is_int($key) ? $value : $key;

                if (class_exists($modelClass)) {
                    $modelClass::observe($listener);
                }
            }
        }

        $this->app->terminating(function () use ($manager): void {
            $manager->saveBufferedLog();
        });
    }
}
