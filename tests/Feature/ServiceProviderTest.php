<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature;

use Curicows\LaravelCommon\LaravelCommonServiceProvider;
use Curicows\LaravelCommon\Tests\TestCase;
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
}
