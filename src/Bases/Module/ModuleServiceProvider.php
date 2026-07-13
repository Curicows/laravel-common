<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases\Module;

use Curicows\LaravelCommon\Bases\BaseServiceProvider;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Str;

abstract class ModuleServiceProvider extends BaseServiceProvider
{
    abstract public function moduleName(): string;

    public function additionalBoot(): void {}

    public function register(): void {}

    public function boot(): void
    {
        $this->bootCommands();
        $this->bootCommandSchedules();
        $this->bootTranslations();
        $this->bootConfig();
        $this->bootViews();
        $this->bootEvents();
        $this->bootSubscribers();
        $this->loadMigrationsFrom(module_path($this->moduleName(), 'database/migrations'));
        $this->bootPolicies();
        $this->additionalBoot();
    }

    /**
     * @return array<class-string, class-string>
     */
    public function events(): array
    {
        return [];
    }

    /**
     * @return array<int, class-string>
     */
    public function subscribers(): array
    {
        return [];
    }

    /**
     * @return array<class-string, class-string>
     */
    public function policies(): array
    {
        return [];
    }

    protected function bootCommands(): void {}

    protected function bootCommandSchedules(): void {}

    protected function bootTranslations(): void
    {
        $langPath = resource_path('lang/modules/'.$this->moduleNameLower());

        if (is_dir($langPath)) {
            $this->loadTranslationsFrom($langPath, $this->moduleNameLower());
            $this->loadJsonTranslationsFrom($langPath);

            return;
        }

        $this->loadTranslationsFrom(module_path($this->moduleName(), 'lang'), $this->moduleNameLower());
        $this->loadJsonTranslationsFrom(module_path($this->moduleName(), 'lang'));
    }

    protected function bootConfig(): void
    {
        $this->publishes([
            module_path($this->moduleName(), 'config/config.php') => config_path($this->moduleNameLower().'.php'),
        ], 'config');
        $this->mergeConfigFrom(module_path($this->moduleName(), 'config/config.php'), $this->moduleNameLower());
    }

    protected function bootViews(): void
    {
        $viewPath = resource_path('views/modules/'.$this->moduleNameLower());
        $sourcePath = module_path($this->moduleName(), 'resources/views');

        $this->publishes([$sourcePath => $viewPath], ['views', $this->moduleNameLower().'-module-views']);

        $this->loadViewsFrom(array_merge($this->getPublishableViewPaths(), [$sourcePath]), $this->moduleNameLower());

        $componentNamespace = str_replace(
            '/',
            '\\',
            config('modules.namespace').'\\'.$this->moduleName().'\\'.ltrim(
                config('modules.paths.generator.component-class.path'),
                config('modules.paths.app_folder', ''),
            ),
        );

        Blade::componentNamespace($componentNamespace, $this->moduleNameLower());
    }

    /**
     * @return array<int, string>
     */
    protected function getPublishableViewPaths(): array
    {
        $paths = [];

        foreach (config('view.paths') as $path) {
            if (is_dir($path.'/modules/'.$this->moduleNameLower())) {
                $paths[] = $path.'/modules/'.$this->moduleNameLower();
            }
        }

        return $paths;
    }

    protected function bootEvents(): void
    {
        foreach ($this->events() as $event => $listener) {
            Event::listen($event, $listener);
        }
    }

    protected function bootSubscribers(): void
    {
        foreach ($this->subscribers() as $subscriber) {
            Event::subscribe($subscriber);
        }
    }

    protected function moduleNameLower(): string
    {
        return Str::kebab($this->moduleName());
    }
}
