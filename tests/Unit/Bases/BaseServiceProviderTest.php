<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\BaseServiceProvider;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(BaseServiceProvider::class)]
class BaseServiceProviderTest extends TestCase
{
    protected function tearDown(): void
    {
        TestBaseServiceProvider::$calls = [];
        TestRegisteredServiceProvider::$registered = false;

        parent::tearDown();
    }

    public function test_register_runs_standard_registration_hooks(): void
    {
        $provider = new TestBaseServiceProvider($this->app);

        $provider->register();

        self::assertSame([
            'registerAliases',
            'registerProviders',
            'additionalRegister',
        ], TestBaseServiceProvider::$calls);
        self::assertTrue(TestRegisteredServiceProvider::$registered);
    }

    public function test_boot_runs_standard_boot_hooks_and_registers_policies(): void
    {
        $provider = new TestBaseServiceProvider($this->app);

        $provider->boot();

        self::assertSame([
            'schedule',
            'blade',
            'gate',
            'additionalBoot',
        ], TestBaseServiceProvider::$calls);
        self::assertInstanceOf(TestPolicy::class, Gate::getPolicyFor(TestModel::class));
    }
}

final class TestBaseServiceProvider extends BaseServiceProvider
{
    /**
     * @var array<int, string>
     */
    public static array $calls = [];

    protected function registerAliases(AliasLoader $loader): void
    {
        self::$calls[] = 'registerAliases';
    }

    protected function registerProviders(): void
    {
        self::$calls[] = 'registerProviders';

        $this->app->register(TestRegisteredServiceProvider::class);
    }

    protected function additionalRegister(): void
    {
        self::$calls[] = 'additionalRegister';
    }

    protected function schedule(): void
    {
        self::$calls[] = 'schedule';
    }

    protected function blade(): void
    {
        self::$calls[] = 'blade';
    }

    protected function gate(): void
    {
        self::$calls[] = 'gate';
    }

    protected function policies(): array
    {
        return [
            TestModel::class => TestPolicy::class,
        ];
    }

    protected function additionalBoot(): void
    {
        self::$calls[] = 'additionalBoot';
    }
}

final class TestRegisteredServiceProvider extends ServiceProvider
{
    public static bool $registered = false;

    public function register(): void
    {
        self::$registered = true;
    }
}

final class TestModel extends Model {}

final class TestPolicy {}
