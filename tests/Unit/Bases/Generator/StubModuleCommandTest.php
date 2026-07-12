<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Curicows\LaravelCommon\Tests\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(StubModuleCommand::class)]
class StubModuleCommandTest extends TestCase
{
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

    private function setCommandInput(ExposedStubModuleCommand $command, array $arguments): void
    {
        $input = new ArrayInput($arguments, $command->getDefinition());
        $output = new NullOutput;

        (new ReflectionProperty($command, 'input'))->setValue($command, $input);
        (new ReflectionProperty($command, 'output'))->setValue($command, $output);
    }
}

final class ExposedStubModuleCommand extends StubModuleCommand
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
