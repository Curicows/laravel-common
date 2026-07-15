<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases\Generator;

class CuricowsStub
{
    protected string $path;

    protected static ?string $basePath = null;

    /**
     * @var array<string, string>
     */
    protected array $replaces = [];

    /**
     * @param  array<string, string>  $replaces
     */
    public function __construct(string $path, array $replaces = [])
    {
        $this->path = $path;
        $this->replaces = $replaces;
    }

    /**
     * @param  array<string, string>  $replaces
     */
    public static function create(string $path, array $replaces = []): self
    {
        return new static($path, $replaces);
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    public function getPath(): string
    {
        return static::getBasePath().$this->path;
    }

    public static function setBasePath(string $path): void
    {
        static::$basePath = $path;
    }

    public static function getBasePath(): ?string
    {
        if (static::$basePath !== null) {
            return static::$basePath;
        }

        $configuredPath = config('laravel-common.stubs.path');

        if (is_string($configuredPath) && is_dir($configuredPath)) {
            return $configuredPath;
        }

        $publishedPath = base_path('stubs/curicows');

        if (is_dir($publishedPath)) {
            return $publishedPath;
        }

        return dirname(__DIR__, 3).'/stubs/curicows';
    }

    public function getContents(): string
    {
        $contents = file_get_contents($this->getPath());

        foreach ($this->replaces as $search => $replace) {
            $contents = str_replace('$'.strtoupper($search).'$', $replace, $contents);
        }

        return $contents;
    }

    public function render(): string
    {
        return $this->getContents();
    }

    public function saveTo(string $path, string $filename): bool
    {
        return (bool) file_put_contents($path.'/'.$filename, $this->getContents());
    }

    /**
     * @param  array<string, string>  $replaces
     */
    public function replace(array $replaces = []): self
    {
        $this->replaces = $replaces;

        return $this;
    }

    /**
     * @return array<string, string>
     */
    public function getReplaces(): array
    {
        return $this->replaces;
    }

    public function __toString(): string
    {
        return $this->render();
    }
}
