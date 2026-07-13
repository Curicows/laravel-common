<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Generator;

use Curicows\LaravelCommon\Bases\Generator\CuricowsStub;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(CuricowsStub::class)]
class CuricowsStubTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('custom-stubs'));

        parent::tearDown();
    }

    public function test_get_base_path_uses_configured_existing_path(): void
    {
        $path = base_path('custom-stubs');

        $this->app->make(Filesystem::class)->ensureDirectoryExists($path);
        config(['laravel-common.stubs.path' => $path]);

        self::assertSame($path, CuricowsStub::getBasePath());
    }

    public function test_get_base_path_falls_back_to_package_stubs(): void
    {
        config(['laravel-common.stubs.path' => base_path('missing-stubs')]);

        self::assertSame(dirname(__DIR__, 4).'/stubs/curicows', CuricowsStub::getBasePath());
    }
}
