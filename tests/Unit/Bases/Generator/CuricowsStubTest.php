<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Generator;

use Curicows\LaravelCommon\Bases\Generator\CuricowsStub;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;

#[CoversClass(CuricowsStub::class)]
class CuricowsStubTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->resetBasePath();
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('custom-stubs'));
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('stubs'));
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('generated-stubs'));

        parent::tearDown();
    }

    public function test_create_set_path_replace_render_string_cast_and_save_to(): void
    {
        $filesystem = $this->app->make(Filesystem::class);
        $basePath = base_path('custom-stubs');
        $destination = base_path('generated-stubs');

        $filesystem->ensureDirectoryExists($basePath.'/nested');
        $filesystem->ensureDirectoryExists($destination);
        file_put_contents($basePath.'/nested/sample.stub', 'Hello $NAME$ from $PLACE$');

        CuricowsStub::setBasePath($basePath);

        $stub = CuricowsStub::create('/missing.stub')
            ->setPath('/nested/sample.stub')
            ->replace(['name' => 'Curicows', 'place' => 'Cosmoem']);

        self::assertSame(['name' => 'Curicows', 'place' => 'Cosmoem'], $stub->getReplaces());
        self::assertSame($basePath.'/nested/sample.stub', $stub->getPath());
        self::assertSame('Hello Curicows from Cosmoem', $stub->getContents());
        self::assertSame('Hello Curicows from Cosmoem', $stub->render());
        self::assertSame('Hello Curicows from Cosmoem', (string) $stub);
        self::assertTrue($stub->saveTo($destination, 'sample.php'));
        self::assertSame('Hello Curicows from Cosmoem', file_get_contents($destination.'/sample.php'));
    }

    public function test_constructor_replacements_are_used(): void
    {
        $filesystem = $this->app->make(Filesystem::class);
        $basePath = base_path('custom-stubs');

        $filesystem->ensureDirectoryExists($basePath);
        file_put_contents($basePath.'/sample.stub', 'Hello $NAME$');

        CuricowsStub::setBasePath($basePath);

        $stub = new CuricowsStub('/sample.stub', ['name' => 'Curicows']);

        self::assertSame('Hello Curicows', $stub->render());
    }

    public function test_get_base_path_uses_configured_existing_path(): void
    {
        $path = base_path('custom-stubs');

        $this->app->make(Filesystem::class)->ensureDirectoryExists($path);
        config(['laravel-common.stubs.path' => $path]);

        self::assertSame($path, CuricowsStub::getBasePath());
    }

    public function test_get_base_path_uses_published_stubs_before_package_stubs(): void
    {
        $path = base_path('stubs/curicows');

        $this->app->make(Filesystem::class)->ensureDirectoryExists($path);
        config(['laravel-common.stubs.path' => base_path('missing-stubs')]);

        self::assertSame($path, CuricowsStub::getBasePath());
    }

    public function test_get_base_path_falls_back_to_package_stubs(): void
    {
        config(['laravel-common.stubs.path' => base_path('missing-stubs')]);

        self::assertSame(dirname(__DIR__, 4).'/stubs/curicows', CuricowsStub::getBasePath());
    }

    private function resetBasePath(): void
    {
        (new ReflectionProperty(CuricowsStub::class, 'basePath'))->setValue(null, null);
    }
}
