<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling;

use Broadway\EventHandling\EventBus;
use Broadway\EventHandling\EventListener;

final class ListenerRegistry implements ListenerRegistryInterface
{
    public function __construct(
        private readonly EventBus $eventBus
    ) {
    }

    public function addListener(EventListener $listener): void
    {
        $this->eventBus->subscribe($listener);
    }
}
