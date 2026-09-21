<?php

namespace Propello\PackageLearningS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Propello\PackageLearningS\AuditManager
 */
class Audit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Propello\PackageLearningS\AuditManager::class;
    }
}
