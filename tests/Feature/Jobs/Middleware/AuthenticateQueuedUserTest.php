<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Feature\Jobs\Middleware;

use Curicows\LaravelCommon\Jobs\Middleware\AuthenticateQueuedUser;
use Curicows\LaravelCommon\Tests\Fixtures\TestContextKeys;
use Curicows\LaravelCommon\Tests\TestCase;
use MrPunyapal\LaravelAuthJobs\Contracts\HasContextKeys;
use PHPUnit\Framework\Attributes\CoversClass;
use stdClass;

#[CoversClass(AuthenticateQueuedUser::class)]
class AuthenticateQueuedUserTest extends TestCase
{
    public function test_job_continues_when_auth_context_is_missing(): void
    {
        $this->app->bind(HasContextKeys::class, TestContextKeys::class);

        $job = new stdClass;
        $middleware = new AuthenticateQueuedUser;
        $handledJob = null;

        $middleware->handle($job, function (object $job) use (&$handledJob): void {
            $handledJob = $job;
        });

        self::assertSame($job, $handledJob);
    }
}
