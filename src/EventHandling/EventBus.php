<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling;

use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus as EventBusInterface;
use Broadway\EventHandling\EventListener;
use Spiral\Core\CoreInterface;

final class EventBus implements EventBusInterface
{
    public function __construct(
        private readonly CoreInterface $core,
        private readonly EventBusInterface $eventBus
    ) {
    }

    public function subscribe(EventListener $eventListener): void
    {
        $this->eventBus->subscribe($eventListener);
    }

    public function publish(DomainEventStream $domainMessages): void
    {
        $this->core->callAction(self::class, 'publish', ['domainMessages' => $domainMessages]);
    }
}
