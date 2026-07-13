<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;

class ServiceMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-service';

    protected $description = 'Create a new Service class for the specified module.';

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.service.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.service.path');
    }

    protected function className(): string
    {
        $modelName = $this->getModelName();

        return "{$modelName}Service";
    }

    protected function getStubName(): string
    {
        return '/service/plain.stub';
    }
}
