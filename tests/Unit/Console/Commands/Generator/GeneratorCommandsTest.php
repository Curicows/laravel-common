<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Console\Commands\Generator;

use Curicows\LaravelCommon\Console\Commands\Generator\ControllerMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\DtoMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\PolicyMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\RepositoryMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\ServiceMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\ViewMakeCommand;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Console\OutputStyle;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\CoversClass;
use ReflectionProperty;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

#[CoversClass(ControllerMakeCommand::class)]
#[CoversClass(DtoMakeCommand::class)]
#[CoversClass(PolicyMakeCommand::class)]
#[CoversClass(RepositoryMakeCommand::class)]
#[CoversClass(ServiceMakeCommand::class)]
#[CoversClass(ViewMakeCommand::class)]
class GeneratorCommandsTest extends TestCase
{
    public function test_controller_command_generates_related_classes_unless_plain(): void
    {
        $command = new ExposedControllerMakeCommand;
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        $command->additionalHandle();

        self::assertSame([
            ['command' => 'curicows:make-repository', 'arguments' => ['name' => 'PokemonRun']],
            ['command' => 'curicows:make-policy', 'arguments' => ['name' => 'PokemonRun']],
        ], $command->calls);
        self::assertSame('Http/Controllers', $command->getDefaultNamespace());
        self::assertSame('app/Http/Controllers', $command->exposedBasePath());
        self::assertSame('PokemonRunController', $command->exposedClassName());
        self::assertSame('/controller/dto.stub', $command->exposedGetStubName());

        $plainCommand = new ExposedControllerMakeCommand;
        $this->setCommandInput($plainCommand, ['name' => 'PokemonRun', 'module' => 'Pokemon', '--plain' => true]);

        $plainCommand->additionalHandle();

        self::assertSame([], $plainCommand->calls);
        self::assertSame('/controller/plain.stub', $plainCommand->exposedGetStubName());
    }

    public function test_dto_command_resolves_names_and_stubs_for_each_type(): void
    {
        self::assertSame(['PokemonRunDto', '/dto/plain.stub'], $this->dtoNameAndStub([]));
        self::assertSame(['CreatePokemonRunDto', '/dto/create.stub'], $this->dtoNameAndStub(['--create' => true]));
        self::assertSame(['SearchPokemonRunDto', '/dto/search.stub'], $this->dtoNameAndStub(['--search' => true]));
        self::assertSame(['PokemonRunDto', '/dto/model.stub'], $this->dtoNameAndStub(['--model' => true]));
        self::assertSame(['UpdatePokemonRunDto', '/dto/update.stub'], $this->dtoNameAndStub(['--update' => true]));

        $command = new ExposedDtoMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('Http/Dtos', $command->getDefaultNamespace());
        self::assertSame('app/Http/Dtos', $command->exposedBasePath());
    }

    public function test_policy_command_resolves_plain_and_model_stubs(): void
    {
        $command = new ExposedPolicyMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('Policies', $command->getDefaultNamespace());
        self::assertSame('app/Policies', $command->exposedBasePath());
        self::assertSame('PokemonRunPolicy', $command->exposedClassName());
        self::assertSame('/policy/model.stub', $command->exposedGetStubName());

        $plainCommand = new ExposedPolicyMakeCommand;
        $this->setCommandInput($plainCommand, ['name' => 'PokemonRun', 'module' => 'Pokemon', '--plain' => true]);

        self::assertSame('/policy/plain.stub', $plainCommand->exposedGetStubName());
    }

    public function test_repository_command_resolves_generation_modes(): void
    {
        $command = new ExposedRepositoryMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        $command->additionalHandle();

        self::assertSame('Http/Repositories', $command->getDefaultNamespace());
        self::assertSame('app/Http/Repositories', $command->exposedBasePath());
        self::assertSame('PokemonRunRepository', $command->exposedClassName());
        self::assertSame('/repository/dto.stub', $command->exposedGetStubName());
        self::assertSame([
            ['command' => 'curicows:make-dto', 'arguments' => ['name' => 'PokemonRun/PokemonRun', '--create' => true]],
            ['command' => 'curicows:make-dto', 'arguments' => ['name' => 'PokemonRun/PokemonRun', '--search' => true]],
            ['command' => 'curicows:make-dto', 'arguments' => ['name' => 'PokemonRun/PokemonRun', '--update' => true]],
            ['command' => 'curicows:make-dto', 'arguments' => ['name' => 'PokemonRun/PokemonRun', '--model' => true]],
        ], $command->calls);

        $plainCommand = new ExposedRepositoryMakeCommand;
        $this->setCommandInput($plainCommand, ['name' => 'PokemonRun', 'module' => 'Pokemon', '--plain' => true]);

        $plainCommand->additionalHandle();

        self::assertSame([], $plainCommand->calls);
        self::assertSame('/repository/plain.stub', $plainCommand->exposedGetStubName());
    }

    public function test_repository_command_can_delegate_request_generation(): void
    {
        $command = new ExposedRepositoryMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon', '--request' => true]);

        Artisan::command('module:make-request {name} {module}', fn (): int => 0);

        $command->additionalHandle();

        self::assertSame([], $command->calls);
        self::assertSame('/repository/request.stub', $command->exposedGetStubName());
    }

    public function test_service_command_resolves_base_path_class_and_stub(): void
    {
        $command = new ExposedServiceMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('Services', $command->getDefaultNamespace());
        self::assertSame('app/Services', $command->exposedBasePath());
        self::assertSame('PokemonRunService', $command->exposedClassName());
        self::assertSame('/service/plain.stub', $command->exposedGetStubName());
    }

    public function test_view_command_resolves_names_stubs_and_paths(): void
    {
        self::assertSame(['.blade', '/view/plain.stub'], $this->viewNameAndStub([]));
        self::assertSame(['create.blade', '/view/create.stub'], $this->viewNameAndStub(['--create' => true]));
        self::assertSame(['edit.blade', '/view/edit.stub'], $this->viewNameAndStub(['--edit' => true]));
        self::assertSame(['index.blade', '/view/list.stub'], $this->viewNameAndStub(['--list' => true]));
        self::assertSame(['show.blade', '/view/show.stub'], $this->viewNameAndStub(['--show' => true]));

        $command = new ExposedViewMakeCommand;
        $this->setCommandInput($command, ['name' => 'Admin/PokemonRun', 'module' => 'Pokemon']);

        self::assertSame('', $command->getDefaultNamespace());
        self::assertSame('resources/views', $command->exposedBasePath());
        self::assertSame('pokemon-run', $command->exposedClassName());
        self::assertSame('admin', $command->exposedGetPath());
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{string, string}
     */
    private function dtoNameAndStub(array $options): array
    {
        $command = new ExposedDtoMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon', ...$options]);

        return [$command->exposedClassName(), $command->exposedGetStubName()];
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array{string, string}
     */
    private function viewNameAndStub(array $options): array
    {
        $command = new ExposedViewMakeCommand;
        $this->setCommandInput($command, ['name' => 'PokemonRun', 'module' => 'Pokemon', ...$options]);

        return [$command->exposedFileName(), $command->exposedGetStubName()];
    }

    private function setCommandInput(ExposedCommandContract $command, array $arguments): void
    {
        $input = new ArrayInput($arguments, $command->getDefinition());
        $output = new OutputStyle($input, new NullOutput);

        (new ReflectionProperty($command, 'input'))->setValue($command, $input);
        (new ReflectionProperty($command, 'output'))->setValue($command, $output);
        $command->setLaravel($this->app);
    }
}

interface ExposedCommandContract
{
    public function getDefinition();

    public function setLaravel($laravel);
}

trait ExposesStubCommandMethods
{
    /**
     * @var array<int, array{command: string, arguments: array<string, mixed>}>
     */
    public array $calls = [];

    protected function callArtisanMake(string $command, array $arguments = []): int
    {
        $this->calls[] = compact('command', 'arguments');

        return 0;
    }

    public function exposedBasePath(): string
    {
        return $this->basePath();
    }

    public function exposedClassName(): string
    {
        return $this->className();
    }

    public function exposedFileName(): string
    {
        return $this->fileName();
    }

    public function exposedGetPath(): string
    {
        return $this->getPath();
    }

    public function exposedGetStubName(): string
    {
        return $this->getStubName();
    }
}

final class ExposedControllerMakeCommand extends ControllerMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}

final class ExposedDtoMakeCommand extends DtoMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}

final class ExposedPolicyMakeCommand extends PolicyMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}

final class ExposedRepositoryMakeCommand extends RepositoryMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}

final class ExposedServiceMakeCommand extends ServiceMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}

final class ExposedViewMakeCommand extends ViewMakeCommand implements ExposedCommandContract
{
    use ExposesStubCommandMethods;
}
