<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use LogicException;
use Spatie\LaravelData\Support\EloquentCasts\DataEloquentCast;

class User extends Authenticatable
{
    use HasUuids;

    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'hidden',
        'two_factor',
        'email_verified_at',
        'user_info_id',
        'created_by',
        'updated_by',
        'deleted_by',
        'google_oauth',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor',
        'google_oauth',
    ];

    /**
     * @return array<string, mixed>
     */
    protected function casts(): array
    {
        $casts = [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];

        if ($twoFactorDto = $this->configuredDataClass('user_two_factor_dto')) {
            $casts['two_factor'] = [DataEloquentCast::class => $twoFactorDto, 'encrypted'];
        }

        if ($googleOAuthDto = $this->configuredDataClass('user_google_oauth_dto')) {
            $casts['google_oauth'] = [DataEloquentCast::class => $googleOAuthDto, 'encrypted'];
        }

        return $casts;
    }

    public static function configuredModelClass(): string
    {
        $model = config('laravel-common.models.user')
            ?: config('auth.providers.users.model')
            ?: self::class;

        return is_string($model) && class_exists($model) ? $model : self::class;
    }

    public function twoFactor(): mixed
    {
        $twoFactor = $this->getAttribute('two_factor');

        if ($twoFactor !== null) {
            return $twoFactor;
        }

        $twoFactorDto = $this->configuredDataClass('user_two_factor_dto');

        if ($twoFactorDto === null) {
            return null;
        }

        if (method_exists($twoFactorDto, 'blank')) {
            return $twoFactorDto::blank();
        }

        return new $twoFactorDto;
    }

    public function verifyPassword(string $password): bool
    {
        return Hash::check($password, $this->password);
    }

    public function changePassword(string $password): void
    {
        $this->password = Hash::make($password);
        $this->save();
    }

    public function updateGoogleOAuth(?string $refreshToken = null): void
    {
        $this->google_oauth = $refreshToken ? $this->newGoogleOAuthData($refreshToken) : null;
        $this->save();
    }

    protected function hasGoogleOauth(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value, array $attributes) => isset($attributes['google_oauth']),
        );
    }

    protected function authorizationRoles(): Attribute
    {
        return Attribute::get(fn () => method_exists($this, 'getRoleNames')
            ? $this->getRoleNames()->values()->all()
            : []);
    }

    protected function authorizationPermissions(): Attribute
    {
        return Attribute::get(fn () => method_exists($this, 'getAllPermissions')
            ? $this->getAllPermissions()->pluck('name')->values()->all()
            : []);
    }

    protected function newGoogleOAuthData(string $refreshToken): mixed
    {
        $googleOAuthDto = $this->configuredDataClass('user_google_oauth_dto');

        if ($googleOAuthDto === null) {
            throw new LogicException('Configure laravel-common.models.user_google_oauth_dto before updating Google OAuth data.');
        }

        $data = [
            'refreshToken' => $refreshToken,
            'createdAt' => now(),
        ];

        if (method_exists($googleOAuthDto, 'from')) {
            return $googleOAuthDto::from($data);
        }

        return new $googleOAuthDto(...$data);
    }

    protected function configuredDataClass(string $key): ?string
    {
        $class = config("laravel-common.models.{$key}");

        return is_string($class) && class_exists($class) ? $class : null;
    }
}
