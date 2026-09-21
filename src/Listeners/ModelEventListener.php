<?php

namespace Propello\PackageLearningS\Listeners;

use Illuminate\Database\Eloquent\Model;
use Propello\PackageLearningS\AuditManager;

class ModelEventListener
{
    public function __construct(protected AuditManager $manager) {}

    public function created(Model $model): void
    {
        $this->manager->record($model, 'created');
    }

    public function updated(Model $model): void
    {
        $this->manager->record($model, 'updated');
    }

    public function deleted(Model $model): void
    {
        $this->manager->record($model, 'deleted');
    }
}
