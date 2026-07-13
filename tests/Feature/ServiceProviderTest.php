<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature;

use Curicows\LaravelCommon\LaravelCommonServiceProvider;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(LaravelCommonServiceProvider::class)]
class ServiceProviderTest extends TestCase
{
    public function test_service_provider_is_registered(): void
    {
        self::assertTrue($this->app->providerIsLoaded(LaravelCommonServiceProvider::class));
    }

    public function test_service_provider_merges_package_config(): void
    {
        self::assertTrue(config('laravel-common.subscribers.authenticate_queued_user'));
        self::assertTrue(config('laravel-common.commands.generator.enabled'));
        self::assertSame(base_path('stubs/curicows'), config('laravel-common.stubs.path'));
        self::assertSame('app/Http/Dtos', config('laravel-common.stubs.generator.dto.path'));
    }

    public function test_service_provider_publishes_package_config(): void
    {
        $paths = LaravelCommonServiceProvider::pathsToPublish(
            LaravelCommonServiceProvider::class,
            'laravel-common-config',
        );

        self::assertSame([
            dirname(__DIR__, 2).'/config/laravel-common.php' => config_path('laravel-common.php'),
        ], $paths);
    }

    public function test_service_provider_publishes_package_stubs(): void
    {
        $paths = LaravelCommonServiceProvider::pathsToPublish(
            LaravelCommonServiceProvider::class,
            'laravel-common-stubs',
        );

        self::assertSame([
            dirname(__DIR__, 2).'/stubs/curicows' => base_path('stubs/curicows'),
        ], $paths);
    }

    public function test_service_provider_registers_generator_commands(): void
    {
        $commands = array_keys(Artisan::all());

        self::assertContains('curicows:make-controller', $commands);
        self::assertContains('curicows:make-dto', $commands);
        self::assertContains('curicows:make-policy', $commands);
        self::assertContains('curicows:make-repository', $commands);
        self::assertContains('curicows:make-service', $commands);
        self::assertContains('curicows:make-view', $commands);
    }
}
