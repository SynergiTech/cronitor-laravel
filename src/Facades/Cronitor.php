<?php

namespace SynergiTech\Cronitor\Laravel\Facades;

use Illuminate\Support\Facades\Facade;
use SynergiTech\Cronitor\Laravel\Cronitor as AccessorClass;

class Cronitor extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AccessorClass::class;
    }
}
