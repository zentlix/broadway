<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling\Processor;

interface ProcessorInterface
{
    public function process(): void;
}
