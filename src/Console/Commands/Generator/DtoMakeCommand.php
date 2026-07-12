<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

// TODO: use a "type" option instead of multiple options
class DtoMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-dto';

    protected $description = 'Create a new DTO class for the specified module.';

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.dto.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.dto.path');
    }

    protected function className(): string
    {
        $modelName = $this->getModelName();
        $dtoType = '';
        if ($this->option('create')) {
            $dtoType = 'Create';
        }
        if ($this->option('search')) {
            $dtoType = 'Search';
        }
        if ($this->option('model')) {
            $dtoType = '';
        }
        if ($this->option('update')) {
            $dtoType = 'Update';
        }

        return Str::studly("$dtoType{$modelName}Dto");
    }

    protected function additionalArguments(): array
    {
        return [];
    }

    protected function additionalOptions(): array
    {
        return [
            ['search', 's', InputOption::VALUE_NONE, 'Generate DTO class to search', null],
            ['create', 'c', InputOption::VALUE_NONE, 'Generate DTO class to create', null],
            ['update', 'u', InputOption::VALUE_NONE, 'Generate DTO class to update', null],
            ['model', 'm', InputOption::VALUE_NONE, 'Generate DTO class to model', null],
        ];
    }

    protected function getStubName(): string
    {
        if ($this->option('create')) {
            return '/dto/create.stub';
        }
        if ($this->option('search')) {
            return '/dto/search.stub';
        }
        if ($this->option('model')) {
            return '/dto/model.stub';
        }
        if ($this->option('update')) {
            return '/dto/update.stub';
        }

        return '/dto/plain.stub';
    }
}
