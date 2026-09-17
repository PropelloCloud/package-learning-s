<?php

namespace Sorayataraszka\PackageLearningS\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Sorayataraszka\PackageLearningS\PackageLearningS
 */
class PackageLearningS extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Sorayataraszka\PackageLearningS\PackageLearningS::class;
    }
}
