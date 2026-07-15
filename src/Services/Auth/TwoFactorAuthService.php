<?php

namespace Curicows\LaravelCommon\Services\Auth;

use BaconQrCode\Renderer\Image\ImagickImageBackEnd;
use Curicows\LaravelCommon\Bases\Service;
use Curicows\LaravelCommon\Enums\Auth\TwoFactorAuthTypeEnum;
use Curicows\LaravelCommon\Http\Dtos\Auth\AuthSessionDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\TwoFactorMethodConfigDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactor\UserOtpDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\TwoFactorChallengeDto;
use Curicows\LaravelCommon\Http\Dtos\Auth\ValidateTwoFactorAuthDto;
use Curicows\LaravelCommon\Models\User;
use Curicows\LaravelCommon\Services\Auth\TwoFactor\OtpTwoFactorMethod;
use Curicows\LaravelCommon\Services\Auth\TwoFactor\TwoFactorMethodRegistry;
use Google2FA;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FAQRCode\Exceptions\MissingQrCodeServiceException;
use PragmaRX\Google2FAQRCode\QRCode\Bacon;
use PragmaRX\Recovery\Recovery;

class TwoFactorAuthService extends Service
{
    private readonly TwoFactorMethodRegistry $methodRegistry;

    public function __construct(
        mixed $userRepositoryOrMethodRegistry = null,
        ?TwoFactorMethodRegistry $methodRegistry = null,
    ) {
        $this->methodRegistry = $methodRegistry
            ?? ($userRepositoryOrMethodRegistry instanceof TwoFactorMethodRegistry
                ? $userRepositoryOrMethodRegistry
                : app(TwoFactorMethodRegistry::class));
    }

    public function shouldChallenge(User $user): bool
    {
        return (bool) config('auth.2fa.enabled') && $this->methodRegistry->availableFor($user)->isNotEmpty();
    }

    public function createChallenge(User $user): TwoFactorChallengeDto
    {
        $method = $this->methodRegistry->defaultFor($user);

        $session = new AuthSessionDto(
            user: $user,
            loggedIn: now(),
            sudoAt: null,
            twoFactorCode: null,
            emailTwoFactorEnabled: $user->twoFactor()->hasMethod(TwoFactorAuthTypeEnum::Email),
            activeTwoFactorMethod: $method?->type(),
            availableTwoFactorMethods: $this->methodRegistry->availableFor($user)
                ->map(fn ($method) => $method->type())
                ->all(),
        );

        if ($method) {
            $session = $method->startChallenge($user, $session);
        }

        session()->put('2fa', $session);

        return TwoFactorChallengeDto::fromSession($session);
    }

    public function validateEmailCode(ValidateTwoFactorAuthDto $data): bool
    {
        $twoFactorSession = $this->getSessionDto();

        $method = $this->methodRegistry->get(TwoFactorAuthTypeEnum::Email);

        if (
            $twoFactorSession
            && $method
            && $twoFactorSession->activeTwoFactorMethod === TwoFactorAuthTypeEnum::Email
            && $method->verify($twoFactorSession->user, $data->code, $twoFactorSession)
        ) {
            $this->forgetSessionDto();

            return true;
        }

        return false;
    }

    public function hasValid2faMail(): bool
    {
        $twoFactorSession = $this->getSessionDto();

        return $twoFactorSession
            && ! $this->sessionExpired($twoFactorSession)
            && $twoFactorSession->hasEmailTwoFactor()
            && $twoFactorSession->activeTwoFactorMethod === TwoFactorAuthTypeEnum::Email
            && ! $twoFactorSession->twoFactorCode?->expired();
    }

    public function hasValid2faOtp(): bool
    {
        $twoFactorSession = $this->getSessionDto();

        return $twoFactorSession
            && ! $this->sessionExpired($twoFactorSession)
            && $twoFactorSession->activeTwoFactorMethod === TwoFactorAuthTypeEnum::Otp
            && $twoFactorSession->hasOtp();
    }

    public function hasValid2faRecovery(): bool
    {
        $twoFactorSession = $this->getSessionDto();

        return $twoFactorSession
            && ! $this->sessionExpired($twoFactorSession)
            && $twoFactorSession->hasOtp();
    }

    public function getSessionDto(): ?AuthSessionDto
    {
        $session = session('2fa');

        return $session instanceof AuthSessionDto ? $session : null;
    }

    public function forgetSessionDto(): void
    {
        session()->forget('2fa');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws InvalidCharactersException
     * @throws SecretKeyTooShortException
     */
    public function otpSecretKey(): string
    {
        return Google2FA::generateSecretKey();
    }

    /**
     * @throws MissingQrCodeServiceException
     */
    public function genQrCode(string $secretKey): string
    {
        return Google2FA::setQrCodeService(new Bacon(
            new ImagickImageBackEnd
        ))->getQRCodeInline(
            config('auth.2fa.name'),
            config('mail.from.address'),
            $secretKey,
        );
    }

    /**
     * @return array<int, string>
     */
    public function genRecoveryCodes(): array
    {
        return new Recovery()->setCount(6)->toArray();
    }

    public function verifyOtp(User $user, string $otpCode, AuthSessionDto $session): bool
    {
        $method = $this->methodRegistry->get(TwoFactorAuthTypeEnum::Otp);

        return $method?->verify($user, $otpCode, $session) ?? false;
    }

    public function verifyRecoveryCode(User $user, string $recoveryCode): bool
    {
        $method = $this->methodRegistry->get(TwoFactorAuthTypeEnum::Otp);

        return $method instanceof OtpTwoFactorMethod && $method->verifyRecoveryCode($user, $recoveryCode);
    }

    public function enableUserEmail(User $user): User
    {
        $user->two_factor = $user->twoFactor()->withMethod(TwoFactorMethodConfigDto::email(configuredAt: now()));
        $user->save();

        return $user;
    }

    public function disableUserEmail(User $user): User
    {
        $user->two_factor = $user->twoFactor()->withoutMethod(TwoFactorAuthTypeEnum::Email);
        $user->save();

        return $user;
    }

    public function requestEmailChallenge(): TwoFactorChallengeDto
    {
        $session = $this->getSessionDto();

        if (! $session || ! $session->hasEmailTwoFactor() || $this->sessionExpired($session)) {
            $this->forgetSessionDto();
            throw $this->badRequest(__('auth.two-factor.unknown-error'));
        }

        $method = $this->methodRegistry->get(TwoFactorAuthTypeEnum::Email);

        if (! $method) {
            $this->forgetSessionDto();
            throw $this->badRequest(__('auth.two-factor.unknown-error'));
        }

        $session->activeTwoFactorMethod = TwoFactorAuthTypeEnum::Email;
        $session = $method->startChallenge($session->user, $session);
        session()->put('2fa', $session);

        return TwoFactorChallengeDto::fromSession($session);
    }

    public function configUserOtp(User $user, string $secretKey): User
    {
        $recoveryCodes = $this->genRecoveryCodes();
        $otp = new UserOtpDto(
            secret: $secretKey,
            recoveryCodes: $recoveryCodes,
            configuredAt: now()
        );
        $user->two_factor = $user->twoFactor()->withMethod(TwoFactorMethodConfigDto::otp($otp));
        $user->save();

        return $user;
    }

    /**
     * @return array<int, string>
     */
    public function enableUserOtp(User $user, string $secretKey, string $code): array
    {
        if (! Google2FA::verifyKey($secretKey, $code)) {
            throw $this->badRequest(__('auth.two-factor.code-incorrect'));
        }

        $recoveryCodes = $this->genRecoveryCodes();
        $otp = new UserOtpDto(
            secret: $secretKey,
            recoveryCodes: $recoveryCodes,
            configuredAt: now()
        );

        $user->two_factor = $user->twoFactor()->withMethod(TwoFactorMethodConfigDto::otp($otp));
        $user->save();

        return $recoveryCodes;
    }

    public function removeUserOtp(User $user): User
    {
        $user->two_factor = $user->twoFactor()->withoutMethod(TwoFactorAuthTypeEnum::Otp);
        $user->save();

        return $user;
    }

    private function sessionExpired(AuthSessionDto $session): bool
    {
        return now()->greaterThan($session->loggedIn->clone()->addMinutes(30));
    }

    private function badRequest(string $message): \Throwable
    {
        $exception = config('laravel-common.two_factor.bad_request_exception');

        if (is_string($exception) && class_exists($exception)) {
            return new $exception($message);
        }

        return new \InvalidArgumentException($message);
    }
}
