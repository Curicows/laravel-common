<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Console\Commands\Generator;

use Curicows\LaravelCommon\Bases\Generator\StubModuleCommand;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;

class ViewMakeCommand extends StubModuleCommand
{
    protected $name = 'curicows:make-view';

    protected $description = 'Create a new view for the specified module.';

    public function getDefaultNamespace(): string
    {
        return config('laravel-common.stubs.generator.view.namespace');
    }

    protected function basePath(): string
    {
        return config('laravel-common.stubs.generator.view.path');
    }

    protected function className(): string
    {
        return Str::kebab($this->getModelName());
    }

    protected function fileName(): string
    {
        $fileName = '';
        if ($this->option('create')) {
            $fileName = 'create';
        }
        if ($this->option('edit')) {
            $fileName = 'edit';
        }
        if ($this->option('list')) {
            $fileName = 'index';
        }
        if ($this->option('show')) {
            $fileName = 'show';
        }

        return Str::kebab("$fileName.blade");
    }

    protected function additionalArguments(): array
    {
        return [];
    }

    protected function additionalOptions(): array
    {
        return [
            ['plain', 'p', InputOption::VALUE_NONE, 'Generate repository class with DTOs', null],
            ['create', null, InputOption::VALUE_NONE, 'Generate create view', null],
            ['edit', null, InputOption::VALUE_NONE, 'Generate edit view', null],
            ['list', null, InputOption::VALUE_NONE, 'Generate list view', null],
            ['show', null, InputOption::VALUE_NONE, 'Generate show view', null],
        ];
    }

    protected function getStubName(): string
    {
        if ($this->option('create')) {
            return '/view/create.stub';
        }
        if ($this->option('edit')) {
            return '/view/edit.stub';
        }
        if ($this->option('list')) {
            return '/view/list.stub';
        }
        if ($this->option('show')) {
            return '/view/show.stub';
        }

        return '/view/plain.stub';
    }

    protected function getPath(): string
    {
        return Str::kebab(parent::getPath());
    }
}
