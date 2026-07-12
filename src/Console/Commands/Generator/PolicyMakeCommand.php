<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class PolicyMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-policy';

    protected $description = 'Create a new Policy class for the specified module.';

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.policy.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.policy.path');
    }

    protected function className(): string
    {
        $modelName = $this->getModelName();

        return Str::studly("{$modelName}Policy");
    }

    protected function additionalOptions(): array
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate plain policy class', null],
        ];
    }

    protected function getStubName(): string
    {
        return $this->option('plain')
            ? '/policy/plain.stub'
            : '/policy/model.stub';
    }
}
