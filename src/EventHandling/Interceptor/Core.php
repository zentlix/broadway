<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling\Interceptor;

use Broadway\Domain\DomainEventStream;
use Broadway\EventHandling\EventBus;
use Spiral\Core\CoreInterface;

/**
 * @psalm-type TParameters = array{domainMessages: DomainEventStream}
 */
final class Core implements CoreInterface
{
    public function __construct(
        private readonly EventBus $eventBus
    ) {
    }

    /**
     * @param-assert TParameters $parameters
     */
    public function callAction(string $controller, string $action, array $parameters = []): bool
    {
        \assert($parameters['domainMessages'] instanceof DomainEventStream);

        $this->eventBus->publish($parameters['domainMessages']);

        return true;
    }
}
