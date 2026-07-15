<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Models;

use Curicows\LaravelCommon\Models\User;
use Curicows\LaravelCommon\Tests\Fixtures\SampleGoogleOAuthDto;
use Curicows\LaravelCommon\Tests\Fixtures\SampleUserTwoFactorDto;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use LogicException;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(User::class)]
class UserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config()->set('app.key', 'base64:'.base64_encode(str_repeat('a', 32)));
        config()->set('laravel-common.models.user_two_factor_dto', SampleUserTwoFactorDto::class);
        config()->set('laravel-common.models.user_google_oauth_dto', SampleGoogleOAuthDto::class);
    }

    public function test_user_exposes_common_fillable_and_hidden_fields(): void
    {
        $user = new User;

        self::assertSame([
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
        ], $user->getFillable());

        self::assertSame([
            'password',
            'remember_token',
            'two_factor',
            'google_oauth',
        ], $user->getHidden());
    }

    public function test_user_casts_configured_auth_data_objects(): void
    {
        $casts = (new User)->getCasts();

        self::assertSame('datetime', $casts['email_verified_at']);
        self::assertSame('hashed', $casts['password']);
        self::assertSame(SampleUserTwoFactorDto::class.':encrypted', $casts['two_factor']);
        self::assertSame(SampleGoogleOAuthDto::class.':encrypted', $casts['google_oauth']);
    }

    public function test_configured_model_class_uses_laravel_common_config_first(): void
    {
        config()->set('laravel-common.models.user', ConfiguredUser::class);
        config()->set('auth.providers.users.model', AuthUser::class);

        self::assertSame(ConfiguredUser::class, User::configuredModelClass());
    }

    public function test_configured_model_class_falls_back_to_auth_provider(): void
    {
        config()->set('laravel-common.models.user', null);
        config()->set('auth.providers.users.model', AuthUser::class);

        self::assertSame(AuthUser::class, User::configuredModelClass());
    }

    public function test_configured_model_class_falls_back_to_common_user(): void
    {
        config()->set('laravel-common.models.user', 'Missing\\User');
        config()->set('auth.providers.users.model', null);

        self::assertSame(User::class, User::configuredModelClass());
    }

    public function test_two_factor_returns_current_value_or_blank_configured_dto(): void
    {
        $user = new User;

        self::assertEquals(new SampleUserTwoFactorDto, $user->twoFactor());

        $dto = new SampleUserTwoFactorDto(['email']);
        $user->setAttribute('two_factor', $dto);

        self::assertEquals($dto, $user->twoFactor());
    }

    public function test_two_factor_returns_null_without_configured_dto(): void
    {
        config()->set('laravel-common.models.user_two_factor_dto', null);

        self::assertNull((new User)->twoFactor());
    }

    public function test_verify_and_change_password_use_laravel_hashing(): void
    {
        $user = new SaveTrackingUser;
        $user->password = Hash::make('old-password');

        self::assertTrue($user->verifyPassword('old-password'));
        self::assertFalse($user->verifyPassword('wrong-password'));

        $user->changePassword('new-password');

        self::assertTrue(Hash::check('new-password', $user->password));
        self::assertTrue($user->saved);
    }

    public function test_update_google_oauth_uses_configured_dto_and_can_clear_it(): void
    {
        $user = new SaveTrackingUser;

        $user->updateGoogleOAuth('refresh-token');

        self::assertInstanceOf(SampleGoogleOAuthDto::class, $user->google_oauth);
        self::assertSame('refresh-token', $user->google_oauth->refreshToken);
        self::assertTrue($user->saved);

        $user->saved = false;
        $user->updateGoogleOAuth();

        self::assertNull($user->google_oauth);
        self::assertTrue($user->saved);
    }

    public function test_update_google_oauth_requires_configured_dto_when_setting_token(): void
    {
        config()->set('laravel-common.models.user_google_oauth_dto', null);

        $this->expectException(LogicException::class);

        (new SaveTrackingUser)->updateGoogleOAuth('refresh-token');
    }

    public function test_common_accessors_expose_google_oauth_and_authorization_data(): void
    {
        $user = new AuthorizationUser;
        $user->setRawAttributes(['google_oauth' => 'stored'], true);

        self::assertTrue($user->has_google_oauth);
        self::assertSame(['admin'], $user->authorization_roles);
        self::assertSame(['users.view', 'users.update'], $user->authorization_permissions);
    }

    public function test_authorization_accessors_return_empty_arrays_when_permission_methods_do_not_exist(): void
    {
        $user = new User;

        self::assertSame([], $user->authorization_roles);
        self::assertSame([], $user->authorization_permissions);
    }
}

class ConfiguredUser extends User {}

class AuthUser extends User {}

class SaveTrackingUser extends User
{
    public bool $saved = false;

    public function save(array $options = []): bool
    {
        $this->saved = true;

        return true;
    }
}

class AuthorizationUser extends User
{
    public function getRoleNames(): Collection
    {
        return collect(['admin']);
    }

    public function getAllPermissions(): Collection
    {
        return collect([
            (object) ['name' => 'users.view'],
            (object) ['name' => 'users.update'],
        ]);
    }
}
