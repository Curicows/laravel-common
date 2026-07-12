<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases\Generator;

use Nwidart\Modules\Support\Stub as BaseStub;

class CuricowsStub extends BaseStub
{
    public static function getBasePath(): ?string
    {
        $configuredPath = config('laravel-common.stubs.path');

        if (is_string($configuredPath) && is_dir($configuredPath)) {
            return $configuredPath;
        }

        $publishedPath = base_path('stubs/curicows');

        if (is_dir($publishedPath)) {
            return $publishedPath;
        }

        return dirname(__DIR__, 3).'/stubs/curicows';
    }
}
