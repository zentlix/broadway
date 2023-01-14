<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\App\Bootloader;

use Spiral\Attributes\Factory;
use Spiral\Attributes\ReaderInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Core\BinderInterface;

class AttributesBootloader extends Bootloader
{
    public function init(BinderInterface $binder): void
    {
        $binder->bindSingleton(
            ReaderInterface::class,
            static function (): ReaderInterface {
                $factory = new Factory();
                return $factory->create();
            }
        );
    }
}
