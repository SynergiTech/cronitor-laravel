<?php

namespace SynergiTech\Cronitor\Laravel\Tests;

use Orchestra\Testbench\TestCase as BaseTestCase;
use SynergiTech\Cronitor\Laravel\CronitorServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [
            CronitorServiceProvider::class,
        ];
    }
}
