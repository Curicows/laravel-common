<?php

namespace Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor;

use Carbon\Carbon;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Spatie\LaravelData\Data;

class TwoFactorMethodConfigDto extends Data
{
    public function __construct(
        public TwoFactorAuthTypeEnum $type,
        public bool $enabled = true,
        public ?Carbon $configuredAt = null,
        /** @var array<string, mixed> */
        public array $data = [],
    ) {}

    public static function email(bool $enabled = true, ?Carbon $configuredAt = null): self
    {
        return new self(
            type: TwoFactorAuthTypeEnum::Email,
            enabled: $enabled,
            configuredAt: $configuredAt,
        );
    }

    public static function otp(UserOtpDto $otp, bool $enabled = true): self
    {
        return new self(
            type: TwoFactorAuthTypeEnum::Otp,
            enabled: $enabled,
            configuredAt: $otp->configuredAt,
            data: [
                'secret' => $otp->secret,
                'recoveryCodes' => $otp->recoveryCodes,
                'configuredAt' => $otp->configuredAt->clone()->utc()->format('Y-m-d\TH:i:s.vP'),
            ],
        );
    }

    public function otpConfig(): ?UserOtpDto
    {
        if ($this->type !== TwoFactorAuthTypeEnum::Otp) {
            return null;
        }

        if (! isset($this->data['secret'], $this->data['recoveryCodes'], $this->data['configuredAt'])) {
            return null;
        }

        return UserOtpDto::from($this->data);
    }

    public function typeName(): string
    {
        return strtolower($this->type->name);
    }
}
