<?php

namespace Propello\PackageLearningS\Facades;

use Illuminate\Support\Facades\Facade;
use Propello\PackageLearningS\AuditManager;

/**
 * @see \Propello\PackageLearningS\AuditManager
 */
class Audit extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return AuditManager::class;
    }
}
