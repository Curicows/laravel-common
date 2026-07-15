<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Illuminate\Support\Collection;
use Spatie\LaravelData\Data;

class UserTwoFactorDto extends Data
{
    /**
     * @param  array<int, TwoFactorMethodConfigDto|array<string, mixed>>  $methods
     */
    public function __construct(
        public array $methods = [],
    ) {
        $this->methods = array_values(array_map(
            fn (TwoFactorMethodConfigDto|array $method) => $method instanceof TwoFactorMethodConfigDto
                ? $method
                : TwoFactorMethodConfigDto::from($method),
            $methods,
        ));
    }

    public static function blank(): self
    {
        return new self;
    }

    /**
     * @return Collection<int, TwoFactorMethodConfigDto>
     */
    public function enabledMethodConfigs(): Collection
    {
        return collect($this->methods)
            ->filter(fn (TwoFactorMethodConfigDto $method) => $method->enabled)
            ->values();
    }

    /**
     * @return array<int, TwoFactorAuthTypeEnum>
     */
    public function enabledMethods(): array
    {
        return $this->enabledMethodConfigs()
            ->map(fn (TwoFactorMethodConfigDto $method) => $method->type)
            ->all();
    }

    public function method(TwoFactorAuthTypeEnum $type): ?TwoFactorMethodConfigDto
    {
        return $this->enabledMethodConfigs()
            ->first(fn (TwoFactorMethodConfigDto $method) => $method->type === $type);
    }

    public function hasMethod(TwoFactorAuthTypeEnum $type): bool
    {
        return $this->method($type) !== null;
    }

    public function withMethod(TwoFactorMethodConfigDto $method): self
    {
        return new self([
            ...$this->enabledMethodConfigs()
                ->reject(fn (TwoFactorMethodConfigDto $existing) => $existing->type === $method->type)
                ->all(),
            $method,
        ]);
    }

    public function withoutMethod(TwoFactorAuthTypeEnum $type): self
    {
        return new self(
            $this->enabledMethodConfigs()
                ->reject(fn (TwoFactorMethodConfigDto $method) => $method->type === $type)
                ->all()
        );
    }
}
