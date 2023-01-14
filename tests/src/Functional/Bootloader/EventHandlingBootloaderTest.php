<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\Functional\Bootloader;

use Broadway\EventHandling\EventBus as EventBusInterface;
use Spiral\Broadway\EventHandling\EventBus;
use Spiral\Broadway\EventHandling\ListenerProcessorRegistry;
use Spiral\Broadway\EventHandling\ListenerRegistry;
use Spiral\Broadway\EventHandling\ListenerRegistryInterface;
use Spiral\Broadway\Tests\Functional\TestCase;

final class EventHandlingBootloaderTest extends TestCase
{
    public function testEventBusShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(EventBusInterface::class, EventBus::class);
    }

    public function testListenerRegistryShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(ListenerRegistryInterface::class, ListenerRegistry::class);
    }

    public function testListenerProcessorRegistryShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(
            ListenerProcessorRegistry::class,
            ListenerProcessorRegistry::class
        );
    }
}
