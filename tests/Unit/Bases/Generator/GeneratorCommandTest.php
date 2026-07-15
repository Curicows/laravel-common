<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Generator;

use Curicows\LaravelCommon\Bases\Generator\GeneratorCommand;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Filesystem\Filesystem;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Console\Tester\CommandTester;

#[CoversClass(GeneratorCommand::class)]
class GeneratorCommandTest extends TestCase
{
    protected function tearDown(): void
    {
        $this->app->make(Filesystem::class)->deleteDirectory(base_path('generated'));

        parent::tearDown();
    }

    public function test_handle_generates_destination_file(): void
    {
        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $status = $tester->execute(['name' => 'Admin/PokemonRun']);

        self::assertSame(GeneratorCommand::SUCCESS, $status);
        self::assertSame('generated', file_get_contents(base_path('generated/Admin/PokemonRun.php')));
    }

    public function test_handle_overwrites_destination_file_with_force(): void
    {
        $file = base_path('generated/Admin/PokemonRun.php');
        $this->app->make(Filesystem::class)->ensureDirectoryExists(dirname($file));
        file_put_contents($file, 'existing');

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $status = $tester->execute(['name' => 'Admin/PokemonRun', '--force' => true]);

        self::assertSame(GeneratorCommand::SUCCESS, $status);
        self::assertSame('generated', file_get_contents($file));
    }

    public function test_handle_generates_file_when_directory_already_exists(): void
    {
        $this->app->make(Filesystem::class)->ensureDirectoryExists(base_path('generated/Admin'));

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $status = $tester->execute(['name' => 'Admin/PokemonRun']);

        self::assertSame(GeneratorCommand::SUCCESS, $status);
        self::assertSame('generated', file_get_contents(base_path('generated/Admin/PokemonRun.php')));
    }

    public function test_handle_returns_failure_when_file_exists_without_force(): void
    {
        $file = base_path('generated/Admin/PokemonRun.php');
        $this->app->make(Filesystem::class)->ensureDirectoryExists(dirname($file));
        file_put_contents($file, 'existing');

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);
        $tester = new CommandTester($command);

        $status = $tester->execute(['name' => 'Admin/PokemonRun']);

        self::assertSame(E_ERROR, $status);
        self::assertSame('existing', file_get_contents($file));
    }

    public function test_namespace_helpers_match_module_paths(): void
    {
        config()->set('modules.namespace', 'Modules');

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun']);

        self::assertSame('Admin', $command->pathNamespace('admin'));
        self::assertSame('Modules\\Pokemon\\Http\\Controllers', $command->moduleNamespace('Pokemon', 'Http/Controllers'));
        self::assertSame('Modules\\Pokemon\\Http\\Fakes\\Admin', $command->getClassNamespace(new TestGeneratorModule('Pokemon')));
    }

    public function test_default_namespace_and_module_helpers(): void
    {
        $this->app->instance('modules', new TestGeneratorModuleRepository);

        $command = new DefaultNamespaceGeneratorCommand;
        $command->setLaravel($this->app);
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('', $command->getDefaultNamespace());
        self::assertSame('Pokemon', $command->module()->getStudlyName());
        self::assertSame('Digimon', $command->module('Digimon')->getStudlyName());
    }

    public function test_module_namespace_uses_default_modules_namespace_when_config_path_is_not_string(): void
    {
        config()->set('modules.paths.modules', ['Modules']);
        config()->set('modules.namespace', null);

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);

        self::assertSame('Pokemon', $command->moduleNamespace(new TestGeneratorModule('Pokemon')));
    }

    public function test_path_helpers_normalize_paths_and_app_paths(): void
    {
        config()->set('modules.paths.app_folder', 'src/App');

        $command = new TestGeneratorCommand;
        $command->setLaravel($this->app);

        self::assertSame('Admin/PokemonRun', $command->cleanPath('/Admin//PokemonRun/'));
        self::assertSame('Admin/PokemonRun', $command->studlyPath('/admin/pokemon-run/'));
        self::assertSame('Admin\\PokemonRun', $command->studlyNamespace('\\admin\\pokemon-run\\'));
        self::assertSame('src/App', $command->appPath());
        self::assertSame('src/App/Models/Pokemon', $command->appPath('app/Models/Pokemon'));
        self::assertSame('src/App/app/Models/Pokemon', $command->appPath('src/App/app/Models/Pokemon'));
        self::assertSame('src/App/app', $command->appPath('src/App/app/'));
    }

    private function setCommandInput(GeneratorCommand $command, array $arguments): void
    {
        $input = new ArrayInput($arguments, $command->getDefinition());
        $output = new NullOutput;

        (new ReflectionProperty($command, 'input'))->setValue($command, $input);
        (new ReflectionProperty($command, 'output'))->setValue($command, $output);
    }
}

final class TestGeneratorCommand extends GeneratorCommand
{
    protected $name = 'test:generator-command';

    protected $argumentName = 'name';

    protected function getArguments(): array
    {
        return [
            ['name', null, 'The class name.'],
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', null, 'Overwrite file.'],
        ];
    }

    public function getDefaultNamespace(): string
    {
        return 'Http/Fakes';
    }

    protected function getTemplateContents(): string
    {
        return 'generated';
    }

    protected function getDestinationFilePath(): string
    {
        return base_path('generated/'.$this->argument('name').'.php');
    }
}

final class TestGeneratorModule
{
    public function __construct(private readonly string $name) {}

    public function getStudlyName(): string
    {
        return $this->name;
    }
}

final class DefaultNamespaceGeneratorCommand extends GeneratorCommand
{
    protected $name = 'test:default-namespace-generator-command';

    protected $argumentName = 'name';

    protected function getArguments(): array
    {
        return [
            ['name', null, 'The class name.'],
            ['module', null, 'The module name.'],
        ];
    }

    public function getModuleName(): string
    {
        return $this->argument('module');
    }

    protected function getTemplateContents(): string
    {
        return 'generated';
    }

    protected function getDestinationFilePath(): string
    {
        return base_path('generated/'.$this->argument('name').'.php');
    }
}

final class TestGeneratorModuleRepository
{
    public function findOrFail(string $name): TestGeneratorModule
    {
        return new TestGeneratorModule($name);
    }
}
