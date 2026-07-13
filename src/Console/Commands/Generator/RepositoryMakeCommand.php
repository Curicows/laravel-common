<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Illuminate\Support\Facades\Artisan;
use Symfony\Component\Console\Input\InputOption;

class RepositoryMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-repository';

    protected $description = 'Create a new Repository class for the specified module.';

    public function additionalHandle(): void
    {
        $modelName = $this->getModelName();
        $module = $this->argument('module');

        // If the 'plain' option is provided, return
        if ($this->option('plain')) {
            return;
        }

        // If the 'request' option is provided, generate DTOs
        if ($this->option('request')) {
            Artisan::call('module:make-request', [
                'name' => "{$modelName}Request",
                'module' => $module,
            ], $this->getOutput());

            return;
        }

        $this->callArtisanMake('curicows:make-dto', [
            'name' => "$modelName/$modelName",
            '--create' => true,
        ]);

        $this->callArtisanMake('curicows:make-dto', [
            'name' => "$modelName/$modelName",
            '--search' => true,
        ]);

        $this->callArtisanMake('curicows:make-dto', [
            'name' => "$modelName/$modelName",
            '--update' => true,
        ]);

        $this->callArtisanMake('curicows:make-dto', [
            'name' => "$modelName/$modelName",
            '--model' => true,
        ]);
    }

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.repository.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.repository.path');
    }

    protected function className(): string
    {
        $modelName = $this->getModelName();

        return "{$modelName}Repository";
    }

    protected function additionalArguments(): array
    {
        return [];
    }

    protected function additionalOptions(): array
    {
        return [
            ['request', 'r', InputOption::VALUE_NONE, 'Generate repository class with Requests', null],
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate repository class with DTOs', null],
        ];
    }

    protected function getStubName(): string
    {
        if ($this->option('request')) {
            return '/repository/request.stub';
        }
        if ($this->option('plain')) {
            return '/repository/plain.stub';
        }

        return '/repository/dto.stub';
    }
}
