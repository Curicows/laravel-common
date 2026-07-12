<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Module;

use Curicows\LaravelCommon\Bases\Module\ModuleServiceProvider;
use Curicows\LaravelCommon\Tests\Fixtures\SampleModuleServiceProvider;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ModuleServiceProvider::class)]
class ModuleServiceProviderTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->instance('modules', new SampleModuleRepository);
    }

    protected function tearDown(): void
    {
        $this->filesystem()->deleteDirectory(base_path('Modules'));
        $this->filesystem()->deleteDirectory(resource_path('lang/modules'));
        $this->filesystem()->deleteDirectory(resource_path('views'));

        parent::tearDown();
    }

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

    public function test_default_register_and_additional_boot_are_noops(): void
    {
        $provider = new SampleModuleServiceProvider($this->app);

        $provider->register();
        $provider->additionalBoot();

        self::assertSame('pokemon-module', $provider->exposedModuleNameLower());
    }

    public function test_boot_registers_module_services_from_resource_translation_path(): void
    {
        ConfiguredSampleModuleServiceProvider::$additionalBootCalled = false;

        $this->createModuleConfig();
        $this->createModuleViewPath();
        $this->createResourceTranslationPath();
        $publishableViewPath = $this->createPublishableViewPath();

        config([
            'modules.namespace' => 'Modules',
            'modules.paths.app_folder' => 'app/',
            'modules.paths.generator.component-class.path' => 'app/View/Components',
            'view.paths' => [$publishableViewPath, resource_path('views/unused')],
        ]);

        Event::shouldReceive('listen')
            ->once()
            ->with(SampleModuleEvent::class, SampleModuleListener::class);
        Event::shouldReceive('subscribe')
            ->once()
            ->with(SampleModuleSubscriber::class);
        Gate::shouldReceive('policy')
            ->once()
            ->with(SampleModuleModel::class, SampleModulePolicy::class);
        Blade::shouldReceive('componentNamespace')
            ->once()
            ->with('Modules\\PokemonModule\\View\\Components', 'pokemon-module');

        $provider = new ConfiguredSampleModuleServiceProvider($this->app);

        $provider->boot();

        self::assertTrue(ConfiguredSampleModuleServiceProvider::$additionalBootCalled);
        self::assertSame(true, config('pokemon-module.enabled'));
        self::assertSame('resource translation', trans('pokemon-module::messages.title'));

        $hints = $this->app['view']->getFinder()->getHints()['pokemon-module'];

        self::assertContains($publishableViewPath.'/modules/pokemon-module', $hints);
        self::assertContains(module_path('PokemonModule', 'resources/views'), $hints);

        self::assertSame(
            [module_path('PokemonModule', 'config/config.php') => config_path('pokemon-module.php')],
            ServiceProvider::pathsToPublish(ConfiguredSampleModuleServiceProvider::class, 'config'),
        );
        self::assertSame(
            [module_path('PokemonModule', 'resources/views') => resource_path('views/modules/pokemon-module')],
            ServiceProvider::pathsToPublish(ConfiguredSampleModuleServiceProvider::class, 'pokemon-module-module-views'),
        );
    }

    public function test_boot_translations_falls_back_to_module_lang_path(): void
    {
        $this->createModuleTranslationPath();

        $provider = new ExposedSampleModuleServiceProvider($this->app);

        $provider->exposedBootTranslations();

        self::assertSame('module translation', trans('pokemon-module::messages.title'));
    }

    public function test_get_publishable_view_paths_returns_only_existing_module_view_paths(): void
    {
        $publishableViewPath = $this->createPublishableViewPath();

        config(['view.paths' => [$publishableViewPath, resource_path('views/missing')]]);

        $provider = new ExposedSampleModuleServiceProvider($this->app);

        self::assertSame([
            $publishableViewPath.'/modules/pokemon-module',
        ], $provider->exposedPublishableViewPaths());
    }

    public function test_get_publishable_view_paths_returns_empty_array_when_no_paths_exist(): void
    {
        config(['view.paths' => [resource_path('views/missing')]]);

        $provider = new ExposedSampleModuleServiceProvider($this->app);

        self::assertSame([], $provider->exposedPublishableViewPaths());
    }

    private function createModuleConfig(): void
    {
        $this->filesystem()->ensureDirectoryExists(module_path('PokemonModule', 'config'));
        $this->filesystem()->put(
            module_path('PokemonModule', 'config/config.php'),
            "<?php\n\nreturn ['enabled' => true];\n",
        );
    }

    private function createModuleViewPath(): void
    {
        $this->filesystem()->ensureDirectoryExists(module_path('PokemonModule', 'resources/views'));
    }

    private function createResourceTranslationPath(): void
    {
        $path = resource_path('lang/modules/pokemon-module/en');

        $this->filesystem()->ensureDirectoryExists($path);
        $this->filesystem()->put($path.'/messages.php', "<?php\n\nreturn ['title' => 'resource translation'];\n");
    }

    private function createModuleTranslationPath(): void
    {
        $path = module_path('PokemonModule', 'lang/en');

        $this->filesystem()->ensureDirectoryExists($path);
        $this->filesystem()->put($path.'/messages.php', "<?php\n\nreturn ['title' => 'module translation'];\n");
    }

    private function createPublishableViewPath(): string
    {
        $path = resource_path('views/vendor');

        $this->filesystem()->ensureDirectoryExists($path.'/modules/pokemon-module');

        return $path;
    }

    private function filesystem(): Filesystem
    {
        return $this->app->make(Filesystem::class);
    }
}

class ExposedSampleModuleServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'PokemonModule';
    }

    public function exposedBootTranslations(): void
    {
        $this->bootTranslations();
    }

    /**
     * @return array<int, string>
     */
    public function exposedPublishableViewPaths(): array
    {
        return $this->getPublishableViewPaths();
    }
}

final class ConfiguredSampleModuleServiceProvider extends ExposedSampleModuleServiceProvider
{
    public static bool $additionalBootCalled = false;

    /**
     * @return array<class-string, class-string>
     */
    public function events(): array
    {
        return [SampleModuleEvent::class => SampleModuleListener::class];
    }

    /**
     * @return array<int, class-string>
     */
    public function subscribers(): array
    {
        return [SampleModuleSubscriber::class];
    }

    /**
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        return [SampleModuleModel::class => SampleModulePolicy::class];
    }

    public function additionalBoot(): void
    {
        self::$additionalBootCalled = true;
    }
}

final class SampleModuleEvent {}

final class SampleModuleListener {}

final class SampleModuleSubscriber {}

final class SampleModuleModel {}

final class SampleModulePolicy {}

final class SampleModuleRepository
{
    public function find(string $name): SampleModule
    {
        return new SampleModule($name);
    }
}

final class SampleModule
{
    public function __construct(private readonly string $name) {}

    public function getPath(): string
    {
        return base_path('Modules/'.$this->name);
    }
}
