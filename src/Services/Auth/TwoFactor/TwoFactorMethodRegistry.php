<?php

namespace Curicows\LaravelCommon\Services\Auth\TwoFactor;

use Curicows\LaravelCommon\Contracts\Auth\TwoFactorMethod;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Models\User;
use Illuminate\Support\Collection;

class TwoFactorMethodRegistry
{
    /**
     * @param  array<int, TwoFactorMethod>|null  $methods
     */
    public function __construct(
        private readonly ?array $methods = null,
    ) {}

    /**
     * @return Collection<int, TwoFactorMethod>
     */
    public function availableFor(User $user): Collection
    {
        return $this->methods()
            ->filter(fn (TwoFactorMethod $method) => $method->isConfiguredFor($user))
            ->values();
    }

    public function get(TwoFactorAuthTypeEnum $type): ?TwoFactorMethod
    {
        return $this->methods()
            ->first(fn (TwoFactorMethod $method) => $method->type() === $type);
    }

    public function defaultFor(User $user): ?TwoFactorMethod
    {
        return $this->availableFor($user)
            ->first(fn (TwoFactorMethod $method) => $method->type() === TwoFactorAuthTypeEnum::Otp)
            ?? $this->availableFor($user)->first();
    }

    /**
     * @return Collection<int, TwoFactorMethod>
     */
    private function methods(): Collection
    {
        return collect($this->methods ?? config('laravel-common.two_factor.methods', [
            EmailTwoFactorMethod::class,
            OtpTwoFactorMethod::class,
        ]))->map(fn (TwoFactorMethod|string $method) => is_string($method) ? app($method) : $method);
    }
}
