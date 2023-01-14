<?php

declare(strict_types=1);

namespace Spiral\Broadway\EventHandling\Attribute;

use Spiral\Attributes\NamedArgumentConstructor;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
#[NamedArgumentConstructor]
final class Listener
{
}
