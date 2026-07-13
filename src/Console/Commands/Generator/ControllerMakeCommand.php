<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ControllerMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-controller';

    protected $description = 'Create a new Controller class for the specified module.';

    public function additionalHandle(): void
    {
        $modelName = $this->getModelName();

        if ($this->option('plain')) {
            return;
        }

        $this->callArtisanMake('curicows:make-repository', ['name' => $modelName]);
        $this->callArtisanMake('curicows:make-policy', ['name' => $modelName]);
    }

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.controller.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.controller.path');
    }

    protected function className(): string
    {
        $modelName = $this->getModelName();

        return Str::studly("{$modelName}Controller");
    }

    protected function additionalOptions(): array
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate plain controller class', null],
        ];
    }

    protected function getStubName(): string
    {
        return $this->option('plain')
            ? '/controller/plain.stub'
            : '/controller/dto.stub';
    }
}
