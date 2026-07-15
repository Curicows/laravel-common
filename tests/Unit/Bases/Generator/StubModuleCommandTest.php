<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(StubModuleCommand::class)]
class StubModuleCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('custom-stubs'));
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('Modules'));

        parent::tearDown();
    }

    public function test_string_replacements_are_generated_for_stub_data(): void
    {
        $command = new ExposedStubModuleCommand;

        self::assertSame([
            'MODEL_SNAKE' => 'pokemon_run',
            'MODEL_CAMEL' => 'pokemonRun',
            'MODEL_KEBAB' => 'pokemon-run',
            'MODEL_STUDLY' => 'PokemonRun',
        ], $command->exposedToCamelSnakeKebabStudly('model', 'PokemonRun'));
    }

    public function test_path_and_model_name_are_derived_from_name_argument(): void
    {
        $command = new ExposedStubModuleCommand;
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('Admin', $command->exposedGetPath());
        self::assertSame('PokemonRun', $command->exposedGetModelName());
    }

    public function test_destination_file_path_uses_module_base_path_and_generated_file_name(): void
    {
        $this->app->instance('modules', new StubModuleRepository);

        $command = new ExposedStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        self::assertSame(
            base_path('Modules/Pokemon/app/Http/Fakes/Admin/PokemonRunFake.php'),
            $command->exposedGetDestinationFilePath(),
        );
    }

    public function test_class_name_uses_filename_option_and_destination_handles_no_nested_path(): void
    {
        $this->app->instance('modules', new StubModuleRepository);

        $command = new FilenameStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('CustomName', $command->exposedGetClassName());
        self::assertSame(
            base_path('Modules/Pokemon/app/Http/Fakes/PokemonRunFake.php'),
            $command->exposedGetDestinationFilePath(),
        );
    }

    public function test_definition_includes_default_and_additional_arguments_and_options(): void
    {
        $command = new ExtraStubModuleCommand;

        self::assertTrue($command->getDefinition()->hasArgument('name'));
        self::assertTrue($command->getDefinition()->hasArgument('module'));
        self::assertTrue($command->getDefinition()->hasArgument('extra'));
        self::assertTrue($command->getDefinition()->hasOption('force'));
        self::assertTrue($command->getDefinition()->hasOption('filename'));
        self::assertTrue($command->getDefinition()->hasOption('extra-option'));
    }

    public function test_get_class_namespace_uses_module_and_path(): void
    {
        config()->set('modules.namespace', 'Modules');

        $command = new ExposedStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('Modules\\Pokemon\\Http\\Fakes\\Admin', $command->getClassNamespace(new StubModule('Pokemon')));
    }

    public function test_default_namespaces_are_generated_from_module(): void
    {
        config()->set('modules.namespace', 'Modules');
        $this->app->instance('modules', new StubModuleRepository);

        $command = new ExposedStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        self::assertSame([
            'DTO_NAMESPACE' => 'Modules\\Pokemon\\Http\\Dtos',
            'REPOSITORY_NAMESPACE' => 'Modules\\Pokemon\\Http\\Repositories',
            'CONTROLLER_NAMESPACE' => 'Modules\\Pokemon\\Http\\Controllers',
            'REQUEST_NAMESPACE' => 'Modules\\Pokemon\\Http\\Requests',
            'MODEL_NAMESPACE' => 'Modules\\Pokemon\\Models',
        ], $command->exposedDefaultNamespaces());
    }

    public function test_additional_stub_data_defaults_to_empty_array(): void
    {
        $command = new DefaultDataStubModuleCommand;

        self::assertSame([], $command->exposedAdditionalStubData());
    }

    public function test_additional_handle_defaults_to_noop(): void
    {
        $command = new DefaultDataStubModuleCommand;

        $command->exposedAdditionalHandle();

        self::assertTrue(true);
    }

    public function test_template_contents_include_default_and_additional_stub_data(): void
    {
        $filesystem = $this->app->make(Filesystem::class);
        $stubPath = base_path('custom-stubs');

        $filesystem->ensureDirectoryExists($stubPath);
        file_put_contents($stubPath.'/fake.stub', '$CLASS_NAMESPACE$|$CLASS$|$MODEL_STUDLY$|$MODULE_KEBAB$|$EXTRA$');
        config(['laravel-common.stubs.path' => $stubPath]);
        config()->set('modules.namespace', 'Modules');
        $this->app->instance('modules', new StubModuleRepository);

        $command = new ExposedStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'PokemonWorld']);

        self::assertSame(
            'Modules\\PokemonWorld\\Http\\Fakes\\Admin|PokemonRunFake|PokemonRun|pokemon-world|extra-value',
            $command->exposedGetTemplateContents(),
        );
    }

    public function test_handle_runs_additional_handle_before_parent_handle(): void
    {
        $filesystem = $this->app->make(Filesystem::class);
        $stubPath = base_path('custom-stubs');

        $filesystem->ensureDirectoryExists($stubPath);
        file_put_contents($stubPath.'/fake.stub', '$CLASS$');
        config(['laravel-common.stubs.path' => $stubPath]);
        $this->app->instance('modules', new StubModuleRepository);

        $command = new ExposedStubModuleCommand;
        $command->setLaravel($this->app);

        $status = $command->run(new ArrayInput(['name' => 'PokemonRun', 'module' => 'Pokemon'], $command->getDefinition()), new NullOutput);

        self::assertSame(StubModuleCommand::SUCCESS, $status);
        self::assertTrue($command->additionalHandleCalled);
        self::assertSame('PokemonRunFake', file_get_contents(base_path('Modules/Pokemon/app/Http/Fakes/PokemonRunFake.php')));
    }

    public function test_call_artisan_make_forwards_module_force_and_arguments(): void
    {
        $command = new CallingStubModuleCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon', '--force' => true]);

        self::assertSame(7, $command->exposedCallArtisanMake('curicows:make-dto', ['name' => 'PokemonRun']));
        self::assertSame([
            'command' => 'curicows:make-dto',
            'arguments' => [
                'module' => 'Pokemon',
                '--force' => true,
                'name' => 'PokemonRun',
            ],
        ], $command->calls[0]);
    }

    private function setCommandInput(ExposedStubModuleCommand $command, array $arguments): void
    {
        $input = new ArrayInput($arguments, $command->getDefinition());
        $output = new NullOutput;

        (new ReflectionProperty($command, 'input'))->setValue($command, $input);
        (new ReflectionProperty($command, 'output'))->setValue($command, $output);
    }
}

class ExposedStubModuleCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-fake';

    public function getDefaultNamespace(): string
    {
        return 'Http/Fakes';
    }

    protected function basePath(): string
    {
        return 'app/Http/Fakes';
    }

    protected function getStubName(): string
    {
        return '/fake.stub';
    }

    protected function className(): string
    {
        return $this->getModelName().'Fake';
    }

    /**
     * @return array<string, string>
     */
    public function exposedToCamelSnakeKebabStudly(string $key, string $value): array
    {
        return $this->toCamelSnakeKebabStudly($key, $value);
    }

    public function exposedGetPath(): string
    {
        return $this->getPath();
    }

    public function exposedGetModelName(): string
    {
        return $this->getModelName();
    }

    public function exposedGetDestinationFilePath(): string
    {
        return $this->getDestinationFilePath();
    }

    public function exposedGetClassName(): string
    {
        return $this->getClassName();
    }

    /**
     * @return array<string, string>
     */
    public function exposedDefaultNamespaces(): array
    {
        return $this->defaultNamespaces();
    }

    public function exposedGetTemplateContents(): string
    {
        return $this->getTemplateContents();
    }

    protected function additionalStubData(): array
    {
        return [
            'EXTRA' => 'extra-value',
        ];
    }

    public bool $additionalHandleCalled = false;

    protected function additionalHandle(): void
    {
        $this->additionalHandleCalled = true;
    }
}

final class ExtraStubModuleCommand extends ExposedStubModuleCommand
{
    protected $name = 'curicows:make-extra-fake';

    protected function additionalArguments(): array
    {
        return [
            ['extra', InputArgument::OPTIONAL, 'Extra argument.'],
        ];
    }

    protected function additionalOptions(): array
    {
        return [
            ['extra-option', null, InputOption::VALUE_NONE, 'Extra option.'],
        ];
    }
}

final class DefaultDataStubModuleCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-default-data-fake';

    protected function basePath(): string
    {
        return 'app/Http/Fakes';
    }

    protected function getStubName(): string
    {
        return '/fake.stub';
    }

    protected function className(): string
    {
        return 'Fake';
    }

    /**
     * @return array<string, string>
     */
    public function exposedAdditionalStubData(): array
    {
        return $this->additionalStubData();
    }

    public function exposedAdditionalHandle(): void
    {
        $this->additionalHandle();
    }
}

final class CallingStubModuleCommand extends ExposedStubModuleCommand
{
    /**
     * @var array<int, array{command: string, arguments: array<string, mixed>}>
     */
    public array $calls = [];

    public function call($command, array $arguments = []): int
    {
        $this->calls[] = compact('command', 'arguments');

        return 7;
    }

    public function exposedCallArtisanMake(string $command, array $arguments = []): int
    {
        return $this->callArtisanMake($command, $arguments);
    }
}

final class FilenameStubModuleCommand extends ExposedStubModuleCommand
{
    public function option($key = null)
    {
        if ($key === 'filename') {
            return 'CustomName';
        }

        return parent::option($key);
    }
}

final class StubModuleRepository
{
    public function getModulePath(string $name): string
    {
        return base_path('Modules/'.$name.'/');
    }

    public function findOrFail(string $name): StubModule
    {
        return new StubModule($name);
    }
}

final class StubModule
{
    public function __construct(private readonly string $name) {}

    public function getStudlyName(): string
    {
        return $this->name;
    }
}
