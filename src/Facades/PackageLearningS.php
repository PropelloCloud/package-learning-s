<?php

namespace Propello\PackageLearningS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Propello\PackageLearningS\PackageLearningS
 */
class PackageLearningS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Propello\PackageLearningS\PackageLearningS::class;
    }
}
