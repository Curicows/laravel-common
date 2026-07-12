<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature\Jobs\Middleware;

use Curicows\LaravelCommon\Jobs\Middleware\AuthenticateQueuedUser;
use Curicows\LaravelCommon\Tests\Fixtures\TestContextKeys;
use Curicows\LaravelCommon\Tests\TestCase;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\StatefulGuard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Context;
use MrPunyapal\LaravelAuthJobs\Contracts\HasContextKeys;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(AuthenticateQueuedUser::class)]
class AuthenticateQueuedUserTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->app->bind(HasContextKeys::class, TestContextKeys::class);
    }

    public function test_job_continues_when_auth_context_is_missing(): void
    {
        $job = new stdClass;
        $middleware = new AuthenticateQueuedUser;
        $handledJob = null;

        $middleware->handle($job, function (object $job) use (&$handledJob): void {
            $handledJob = $job;
        });

        self::assertSame($job, $handledJob);
    }

    public function test_job_continues_when_context_guard_cannot_be_replayed(): void
    {
        $auth = new FakeAuthManager([
            'token' => new stdClass,
        ]);

        Auth::swap($auth);
        Context::addHidden(TestContextKeys::authGuardKey(), 'token');
        Context::addHidden(TestContextKeys::authIdKey(), 10);

        $job = new stdClass;
        $handledJob = null;

        (new AuthenticateQueuedUser)->handle($job, function (object $job) use (&$handledJob): void {
            $handledJob = $job;
        });

        self::assertSame($job, $handledJob);
        self::assertSame([
            ['guard', 'token'],
        ], $auth->calls);
    }

    public function test_job_replays_stateful_context_guard(): void
    {
        $guard = new FakeStatefulGuard;
        $auth = new FakeAuthManager([
            'web' => $guard,
        ]);

        Auth::swap($auth);
        Context::addHidden(TestContextKeys::authGuardKey(), 'web');
        Context::addHidden(TestContextKeys::authIdKey(), 10);

        $job = new stdClass;
        $handledJob = null;

        (new AuthenticateQueuedUser)->handle($job, function (object $job) use (&$handledJob): void {
            $handledJob = $job;
        });

        self::assertSame($job, $handledJob);
        self::assertSame([10], $guard->onceUsingIds);
        self::assertSame([
            ['guard', 'web'],
            ['getDefaultDriver'],
            ['shouldUse', 'web'],
            ['guard', 'web'],
            ['forgetGuards'],
            ['shouldUse', 'default'],
        ], $auth->calls);
    }

    public function test_job_replays_matching_stateful_guard_provider(): void
    {
        config()->set('auth.guards.token.provider', 'users');
        config()->set('auth.guards.web.provider', 'users');

        $guard = new FakeStatefulGuard;
        $auth = new FakeAuthManager([
            'token' => new stdClass,
            'web' => $guard,
        ]);

        Auth::swap($auth);
        Context::addHidden(TestContextKeys::authGuardKey(), 'token');
        Context::addHidden(TestContextKeys::authIdKey(), 10);

        $job = new stdClass;
        $handledJob = null;

        (new AuthenticateQueuedUser)->handle($job, function (object $job) use (&$handledJob): void {
            $handledJob = $job;
        });

        self::assertSame($job, $handledJob);
        self::assertSame([10], $guard->onceUsingIds);
        self::assertSame([
            ['guard', 'token'],
            ['guard', 'web'],
            ['getDefaultDriver'],
            ['shouldUse', 'web'],
            ['guard', 'web'],
            ['forgetGuards'],
            ['shouldUse', 'default'],
        ], $auth->calls);
    }
}

final class FakeAuthManager
{
    /**
     * @param  array<string, object>  $guards
     */
    public function __construct(
        private readonly array $guards,
        private readonly string $defaultDriver = 'default',
    ) {}

    /**
     * @var array<int, array<int, string>>
     */
    public array $calls = [];

    public function getDefaultDriver(): string
    {
        $this->calls[] = ['getDefaultDriver'];

        return $this->defaultDriver;
    }

    public function guard(string $name): object
    {
        $this->calls[] = ['guard', $name];

        return $this->guards[$name] ?? new stdClass;
    }

    public function shouldUse(string $name): void
    {
        $this->calls[] = ['shouldUse', $name];
    }

    public function forgetGuards(): void
    {
        $this->calls[] = ['forgetGuards'];
    }
}

final class FakeStatefulGuard implements StatefulGuard
{
    /**
     * @var array<int, mixed>
     */
    public array $onceUsingIds = [];

    public function check(): bool
    {
        return false;
    }

    public function guest(): bool
    {
        return true;
    }

    public function user(): ?Authenticatable
    {
        return null;
    }

    public function id(): int|string|null
    {
        return null;
    }

    public function validate(array $credentials = []): bool
    {
        return false;
    }

    public function hasUser(): bool
    {
        return false;
    }

    public function setUser(Authenticatable $user): static
    {
        return $this;
    }

    public function attempt(array $credentials = [], $remember = false): bool
    {
        return false;
    }

    public function once(array $credentials = []): bool
    {
        return false;
    }

    public function login(Authenticatable $user, $remember = false): void {}

    public function loginUsingId($id, $remember = false): Authenticatable|false
    {
        return false;
    }

    public function onceUsingId($id): Authenticatable|false
    {
        $this->onceUsingIds[] = $id;

        return false;
    }

    public function viaRemember(): bool
    {
        return false;
    }

    public function logout(): void {}
}
