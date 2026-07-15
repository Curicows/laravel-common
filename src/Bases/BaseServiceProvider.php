<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Bases;

use Illuminate\Foundation\AliasLoader;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

abstract class BaseServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerAliases(AliasLoader::getInstance());
        $this->registerProviders();
        $this->additionalRegister();
    }

    public function boot(): void
    {
        $this->schedule();
        $this->blade();
        $this->gate();
        $this->bootPolicies();
        $this->additionalBoot();
    }

    protected function registerAliases(AliasLoader $loader): void {}

    protected function registerProviders(): void {}

    protected function additionalRegister(): void {}

    protected function schedule(): void {}

    protected function blade(): void {}

    protected function gate(): void {}

    /**
     * @return array<class-string, class-string>
     */
    protected function policies(): array
    {
        return [];
    }

    protected function bootPolicies(): void
    {
        foreach ($this->policies() as $model => $policy) {
            Gate::policy($model, $policy);
        }
    }

    protected function additionalBoot(): void {}
}
