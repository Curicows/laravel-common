<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Carbon\Carbon;
use Spatie\LaravelData\Data;

class TwoFactorMethodDto extends Data
{
    public function __construct(
        public readonly string $type,
        public readonly bool $enabled,
        public readonly ?Carbon $configuredAt,
    ) {}

    public static function fromConfig(TwoFactorMethodConfigDto $config): self
    {
        return new self(
            type: $config->typeName(),
            enabled: $config->enabled,
            configuredAt: $config->configuredAt,
        );
    }
}
