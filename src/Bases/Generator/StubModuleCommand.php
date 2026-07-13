<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases\Generator;

use Illuminate\Support\Str;
use Nwidart\Modules\Laravel\LaravelFileRepository;
use Nwidart\Modules\Module;
use Nwidart\Modules\Traits\ModuleCommandTrait;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

/**
 * TODO: allow create files without the module
 * TODO: module can be nullable to allow generations on the base path
 */
abstract class StubModuleCommand extends GeneratorCommand
{
    use ModuleCommandTrait;

    protected $argumentName = 'name';

    public function handle(): int
    {
        $this->additionalHandle();

        return parent::handle();
    }

    /**
     * Get class namespace.
     *
     * @param  Module  $module
     */
    public function getClassNamespace($module): string
    {
        $pathNamespace = $this->pathNamespace($this->getPath());

        return $this->moduleNamespace($module->getStudlyName(), $this->getDefaultNamespace().($pathNamespace ? '\\'.$pathNamespace : ''));
    }

    abstract protected function basePath(): string;

    abstract protected function getStubName(): string;

    abstract protected function className(): string;

    protected function additionalOptions(): array
    {
        return [];
    }

    protected function additionalArguments(): array
    {
        return [];
    }

    protected function additionalHandle(): void {}

    protected function getArguments(): array
    {
        return [
            ['name', InputArgument::REQUIRED, 'The name of the model.'],
            ['module', InputArgument::REQUIRED, 'The name of module will be used.'],
            ...$this->additionalArguments(),
        ];
    }

    protected function getOptions(): array
    {
        return [
            ['force', 'f', InputOption::VALUE_NONE, 'Create the file even if it already exists'],
            ['filename', null, InputOption::VALUE_REQUIRED, 'The file name'],
            ...$this->additionalOptions(),
        ];
    }

    protected function getDestinationFilePath(): string
    {
        /** @var LaravelFileRepository $modules */
        $modules = $this->laravel['modules'];

        $modulePath = $modules->getModulePath($this->getModuleName());
        $basePath = $this->basePath();
        $filePath = $this->getPath() ? $this->getPath().'/' : '';
        $fileName = $this->fileName();

        return "$modulePath$basePath/$filePath$fileName.php";
    }

    protected function fileName(): string
    {
        return $this->className();
    }

    /**
     * @return array<string, string>
     */
    protected function additionalStubData(): array
    {
        return [];
    }

    protected function getTemplateContents(): string
    {
        $module = $this->module();
        $additionalStubData = $this->additionalStubData();

        return new CuricowsStub($this->getStubName(), [
            'CLASS_NAMESPACE' => $this->getClassNamespace($module),
            'CLASS' => $this->getClassName(),
            ...$this->defaultNamespaces(),
            ...$this->toCamelSnakeKebabStudly('MODEL', $this->getModelName()),
            ...$this->toCamelSnakeKebabStudly('MODULE', $this->getModuleName()),
            ...$additionalStubData,
        ])->render();
    }

    /**
     * @return array<string, string>
     */
    protected function toCamelSnakeKebabStudly(string $key, string $value): array
    {
        $upperKey = Str::upper($key);

        return [
            "{$upperKey}_SNAKE" => Str::snake($value),
            "{$upperKey}_CAMEL" => Str::camel($value),
            "{$upperKey}_KEBAB" => Str::kebab($value),
            "{$upperKey}_STUDLY" => Str::studly($value),
        ];
    }

    protected function defaultNamespaces(): array
    {
        $module = $this->module();

        return [
            'DTO_NAMESPACE' => $this->moduleNamespace($module, 'Http/Dtos'),
            'REPOSITORY_NAMESPACE' => $this->moduleNamespace($module, 'Http/Repositories'),
            'CONTROLLER_NAMESPACE' => $this->moduleNamespace($module, 'Http/Controllers'),
            'REQUEST_NAMESPACE' => $this->moduleNamespace($module, 'Http/Requests'),
            'MODEL_NAMESPACE' => $this->moduleNamespace($module, 'Models'),
        ];
    }

    protected function getClassName(): string
    {
        return $this->option('filename')
            ? $this->option('filename')
            : $this->className();
    }

    protected function getModelName(): string
    {
        $path = $this->getPath();

        return class_basename(Str::replaceFirst("$path/", '', $this->argument($this->argumentName)));
    }

    /**
     * Explode the argument name, then reverse the array resulted
     * and unset the first key to always remove the model name.
     * Then return is the array reversed to normal then imploded with a slash "/"
     */
    protected function getPath(): string
    {
        $fullPath = array_reverse(explode('/', $this->argument($this->argumentName)));
        // remove model name from the path
        unset($fullPath[0]);

        return implode('/', array_reverse($fullPath));
    }

    protected function callArtisanMake(string $command, array $arguments = []): int
    {
        return $this->call($command, [
            'module' => $this->argument('module'),
            '--force' => (bool) $this->option('force'),
            ...$arguments,
        ]);
    }
}
