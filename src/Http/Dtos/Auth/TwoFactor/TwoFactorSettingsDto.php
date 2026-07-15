<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Curicows\LaravelCommon\Bases\Dto;
use Curicows\LaravelCommon\Models\User;

class TwoFactorSettingsDto extends Dto
{
    /**
     * @param  array<int, array{type: string, enabled: bool, configuredAt: mixed}>  $methods
     * @param  array<int, string>  $types
     */
    public function __construct(
        public readonly array $methods,
        public readonly array $types,
    ) {}

    public static function fromUser(User $user): self
    {
        $methods = $user->twoFactor()
            ->enabledMethodConfigs()
            ->map(fn (TwoFactorMethodConfigDto $method) => TwoFactorMethodDto::fromConfig($method)->toArray())
            ->values()
            ->all();

        return new self(
            methods: $methods,
            types: array_map(fn (array $method) => $method['type'], $methods),
        );
    }
}
