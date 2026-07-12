<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon;

use Illuminate\Support\ServiceProvider;

class LaravelCommonServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/laravel-common.php', 'laravel-common');
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__.'/../config/laravel-common.php' => config_path('laravel-common.php'),
        ], 'laravel-common-config');
    }
}
