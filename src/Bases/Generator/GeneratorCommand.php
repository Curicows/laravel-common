<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases\Generator;

use Curicows\LaravelCommon\Bases\BaseCommand;
use Exception;
use Illuminate\Support\Str;

abstract class GeneratorCommand extends BaseCommand
{
    /**
     * The name of the class argument.
     */
    protected $argumentName = '';

    abstract protected function getTemplateContents(): string;

    abstract protected function getDestinationFilePath(): string;

    public function handle(): int
    {
        $path = str_replace('\\', '/', $this->getDestinationFilePath());
        $files = $this->laravel['files'];

        if (! $files->isDirectory($directory = dirname($path))) {
            $files->makeDirectory($directory, 0777, true);
        }

        try {
            $this->components->task("Generating file {$path}", function () use ($files, $path): void {
                $overwriteFile = $this->hasOption('force') ? (bool) $this->option('force') : false;

                if ($files->exists($path) && ! $overwriteFile) {
                    throw new Exception('File already exists.');
                }

                $files->put($path, $this->getTemplateContents());
            });
        } catch (Exception) {
            return $this->failure("File : {$path} already exists.", E_ERROR);
        }

        return self::SUCCESS;
    }

    public function getClass(): string
    {
        return class_basename($this->argument($this->argumentName));
    }

    public function getDefaultNamespace(): string
    {
        return '';
    }

    public function getClassNamespace(object $module): string
    {
        $pathNamespace = $this->pathNamespace(str_replace($this->getClass(), '', $this->argument($this->argumentName)));

        return $this->moduleNamespace($module->getStudlyName(), $this->getDefaultNamespace().($pathNamespace ? '\\'.$pathNamespace : ''));
    }

    public function module(?string $name = null): object
    {
        return $this->laravel['modules']->findOrFail($name ?? $this->getModuleName());
    }

    public function studlyPath(string $path, string $separator = '/'): string
    {
        return collect(explode($separator, $this->cleanPath($path, $separator)))
            ->map(fn (string $path): string => Str::studly($path))
            ->implode($separator);
    }

    public function studlyNamespace(string $namespace, string $separator = '\\'): string
    {
        return $this->studlyPath($namespace, $separator);
    }

    public function pathNamespace(string $path): string
    {
        return Str::of($this->studlyPath($path))->replace('/', '\\')->trim('\\')->toString();
    }

    public function moduleNamespace(string|object $module, ?string $path = null): string
    {
        $modulesPath = config('modules.paths.modules');
        $defaultNamespace = is_string($modulesPath) ? $this->pathNamespace($modulesPath) : 'Modules';
        $moduleName = is_object($module) && method_exists($module, 'getStudlyName')
            ? $module->getStudlyName()
            : (string) $module;

        $moduleNamespace = config('modules.namespace', $defaultNamespace).'\\'.$moduleName;
        $moduleNamespace .= $path ? '\\'.$this->pathNamespace($path) : '';

        return $this->studlyNamespace($moduleNamespace);
    }

    public function cleanPath(string $path, string $separator = '/'): string
    {
        return Str::of($path)
            ->explode($separator)
            ->reject(fn (string $path): bool => $path === '')
            ->implode($separator);
    }

    public function appPath(?string $path = null): string
    {
        $configuredPath = config('modules.paths.app_folder');
        $appPath = is_string($configuredPath) && $configuredPath !== '' ? $configuredPath : 'app/';

        if ($path) {
            $replacements = array_unique([$this->cleanPath($appPath).'/', 'app/']);

            do {
                $path = Str::of($path)->replaceStart($appPath, '')->replaceStart('app/', '')->toString();
            } while (Str::of($path)->startsWith($replacements));

            $appPath .= $path !== '' ? '/'.$path : '';
        }

        return $this->cleanPath($appPath);
    }
}
