<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling\Processor;

use Broadway\EventHandling\EventListener;
use Spiral\Attributes\ReaderInterface;
use Spiral\Broadway\EventHandling\Attribute\Listener;
use Spiral\Broadway\EventHandling\ListenerRegistryInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Tokenizer\TokenizationListenerInterface;
use Spiral\Tokenizer\TokenizerListenerRegistryInterface;

final class AttributeProcessor implements TokenizationListenerInterface, ProcessorInterface
{
    /** @var \ReflectionClass[] */
    private array $listeners = [];
    private bool $collected = false;

    public function __construct(
        TokenizerListenerRegistryInterface $listenerRegistry,
        private readonly ReaderInterface $reader,
        private readonly FactoryInterface $factory,
        private readonly ListenerRegistryInterface $registry
    ) {
        $listenerRegistry->addListener($this);
    }

    public function process(): void
    {
        if (!$this->collected) {
            throw new \RuntimeException(sprintf('Tokenizer did not finalize %s listener.', self::class));
        }

        foreach ($this->listeners as $ref) {
            $listener = $this->factory->make($ref->getName());

            \assert($listener instanceof EventListener);
            $this->registry->addListener($listener);
        }
    }

    public function listen(\ReflectionClass $class): void
    {
        $attr = $this->reader->firstClassMetadata($class, Listener::class);

        if ($attr instanceof Listener) {
            $this->listeners[] = $class;
        }
    }

    public function finalize(): void
    {
        $this->collected = true;
    }
}
