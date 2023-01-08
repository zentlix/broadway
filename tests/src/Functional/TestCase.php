<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\Functional;

use Spiral\Broadway\Bootloader\BroadwayBootloader;

abstract class TestCase extends \Spiral\Testing\TestCase
{
    public function rootDirectory(): string
    {
        return __DIR__ . '/../';
    }

    public function defineBootloaders(): array
    {
        return [
            BroadwayBootloader::class,
        ];
    }
}
