<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling;

use Spiral\Broadway\EventHandling\Processor\ProcessorInterface;
use Spiral\Core\Container\SingletonInterface;

final class ListenerProcessorRegistry implements ProcessorInterface, SingletonInterface
{
    /** @var ProcessorInterface[] */
    private array $processors = [];
    private bool $processed = false;

    public function addProcessor(ProcessorInterface $processor): void
    {
        $this->processors[] = $processor;
    }

    public function process(): void
    {
        if ($this->processed) {
            return;
        }

        foreach ($this->processors as $processor) {
            $processor->process();
        }

        $this->processed = true;
    }

    public function isProcessed(): bool
    {
        return $this->processed;
    }

    public function getProcessors(): array
    {
        return $this->processors;
    }
}
