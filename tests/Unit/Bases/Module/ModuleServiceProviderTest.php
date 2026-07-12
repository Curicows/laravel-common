<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Module;

use Curicows\LaravelCommon\Bases\Module\ModuleServiceProvider;
use Curicows\LaravelCommon\Tests\Fixtures\SampleModuleServiceProvider;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModuleServiceProvider::class)]
class ModuleServiceProviderTest extends TestCase
{
    public function test_module_service_provider_extends_laravel_service_provider(): void
    {
        self::assertInstanceOf(ServiceProvider::class, new SampleModuleServiceProvider($this->app));
    }

    public function test_module_name_lower_uses_kebab_case_module_name(): void
    {
        $provider = new SampleModuleServiceProvider($this->app);

        self::assertSame('pokemon-module', $provider->exposedModuleNameLower());
    }

    public function test_module_service_provider_has_empty_extension_points_by_default(): void
    {
        $provider = new SampleModuleServiceProvider($this->app);

        self::assertSame([], $provider->events());
        self::assertSame([], $provider->subscribers());
        self::assertSame([], $provider->policies());
    }
}
