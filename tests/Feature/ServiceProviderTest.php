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
}
