<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling;

use Broadway\EventHandling\EventListener;

interface ListenerRegistryInterface
{
    public function addListener(EventListener $listener): void;
}
