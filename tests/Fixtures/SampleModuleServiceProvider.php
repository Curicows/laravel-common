<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Curicows\LaravelCommon\Bases\Module\ModuleServiceProvider;

final class SampleModuleServiceProvider extends ModuleServiceProvider
{
    public function moduleName(): string
    {
        return 'PokemonModule';
    }

    public function exposedModuleNameLower(): string
    {
        return $this->moduleNameLower();
    }
}
