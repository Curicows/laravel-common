<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon;

use Curicows\LaravelCommon\Console\Commands\Generator\ControllerMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\DtoMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\PolicyMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\RepositoryMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\ServiceMakeCommand;
use Curicows\LaravelCommon\Console\Commands\Generator\ViewMakeCommand;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\ServiceProvider;

class LaravelCommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(dirname(__DIR__).'/config/laravel-common.php', 'laravel-common');
    }

    public function boot(): void
    {
        $this->registerBlueprintMacros();

        $this->publishes([
            dirname(__DIR__).'/config/laravel-common.php' => config_path('laravel-common.php'),
        ], 'laravel-common-config');

        $this->publishes([
            dirname(__DIR__).'/stubs/curicows' => base_path('stubs/curicows'),
        ], 'laravel-common-stubs');

        $this->publishes([
            dirname(__DIR__).'/database/migrations' => database_path('migrations'),
        ], 'laravel-common-migrations');

        if ($this->app->runningInConsole() && config('laravel-common.commands.generator.enabled', true)) {
            $this->commands([
                ControllerMakeCommand::class,
                DtoMakeCommand::class,
                PolicyMakeCommand::class,
                RepositoryMakeCommand::class,
                ServiceMakeCommand::class,
                ViewMakeCommand::class,
            ]);
        }
    }

    private function registerBlueprintMacros(): void
    {
        Blueprint::macro('createdBy', function (): void {
            $this->foreignUuid('created_by')->nullable();
            $this->foreignUuid('updated_by')->nullable();
            $this->foreignUuid('deleted_by')->nullable();
        });
    }
}
