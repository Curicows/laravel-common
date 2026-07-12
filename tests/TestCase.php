<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests;

use Curicows\LaravelCommon\LaravelCommonServiceProvider;
use Illuminate\Foundation\Application;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Spatie\LaravelData\LaravelDataServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    /**
     * @param  Application  $app
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [
            LaravelCommonServiceProvider::class,
            LaravelDataServiceProvider::class,
        ];
    }
}
