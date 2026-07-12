<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Subscriber;
use Curicows\LaravelCommon\Tests\Fixtures\SampleEvent;
use Curicows\LaravelCommon\Tests\Fixtures\SampleSubscriber;
use Illuminate\Contracts\Queue\ShouldQueueAfterCommit;
use Illuminate\Events\Dispatcher;
use Illuminate\Queue\Attributes\Queue;
use Illuminate\Queue\Attributes\Tries;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

#[CoversClass(Subscriber::class)]
class SubscriberTest extends TestCase
{
    public function test_subscriber_is_queued_after_commit_with_default_queue_configuration(): void
    {
        $subscriber = new SampleSubscriber;
        $reflection = new ReflectionClass(Subscriber::class);

        self::assertInstanceOf(ShouldQueueAfterCommit::class, $subscriber);
        self::assertCount(1, $reflection->getAttributes(Tries::class));
        self::assertCount(1, $reflection->getAttributes(Queue::class));
    }

    public function test_subscriber_keeps_app_specific_middleware_out_by_default(): void
    {
        self::assertSame([], (new SampleSubscriber)->middleware());
    }

    public function test_subscriber_returns_event_handler_map(): void
    {
        $events = new Dispatcher;

        self::assertSame([
            SampleEvent::class => 'handleSampleEvent',
        ], (new SampleSubscriber)->subscribe($events));
    }
}
