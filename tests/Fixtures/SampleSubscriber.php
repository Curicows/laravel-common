<?php

declare(strict_types=1);

namespace Curicows\LaravelCommon\Tests\Fixtures;

use Curicows\LaravelCommon\Bases\Subscriber;
use Illuminate\Events\Dispatcher;

final class SampleSubscriber extends Subscriber
{
    public function subscribe(Dispatcher $events): array
    {
        return [
            SampleEvent::class => 'handleSampleEvent',
        ];
    }

    public function handleSampleEvent(SampleEvent $event): void {}
}
