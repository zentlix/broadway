<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling\Processor;

use Broadway\EventHandling\EventListener;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Broadway\EventHandling\ListenerRegistryInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;

final class ConfigProcessor implements ProcessorInterface
{
    public function __construct(
        private readonly BroadwayConfig $config,
        private readonly FactoryInterface $factory,
        private readonly ListenerRegistryInterface $registry
    ) {
    }

    public function process(): void
    {
        foreach ($this->config->getDomainListeners() as $listener) {
            $listener = match (true) {
                \is_string($listener) => $this->factory->make($listener),
                $listener instanceof Autowire => $listener->resolve($this->factory),
                default => $listener
            };

            \assert($listener instanceof EventListener);
            $this->registry->addListener($listener);
        }
    }
}
