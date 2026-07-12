<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Unit\Bases;

use Curicows\LaravelCommon\Bases\Event;
use Curicows\LaravelCommon\Tests\Fixtures\SampleEvent;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Event::class)]
class EventTest extends TestCase
{
    public function test_event_uses_laravel_event_traits_and_broadcasts_nowhere_by_default(): void
    {
        $event = new SampleEvent;

        self::assertSame([], $event->broadcastOn());
        self::assertContains(Dispatchable::class, class_uses(Event::class));
        self::assertContains(InteractsWithSockets::class, class_uses(Event::class));
        self::assertContains(SerializesModels::class, class_uses(Event::class));
    }
}
