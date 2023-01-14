<?php

declare(strict_types=1);

namespace Spiral\Broadway\Bootloader;

use Broadway\EventHandling\EventBus as EventBusInterface;
use Broadway\EventHandling\SimpleEventBus;
use Spiral\Boot\AbstractKernel;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Broadway\EventHandling\EventBus;
use Spiral\Broadway\EventHandling\Interceptor\Core;
use Spiral\Broadway\EventHandling\ListenerProcessorRegistry;
use Spiral\Broadway\EventHandling\ListenerRegistry;
use Spiral\Broadway\EventHandling\ListenerRegistryInterface;
use Spiral\Broadway\EventHandling\Processor\ProcessorInterface;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Config\Patch\Append;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\FactoryInterface;
use Spiral\Core\InterceptableCore;

/**
 * @psalm-import-type TInterceptor from BroadwayConfig
 */
final class EventHandlingBootloader extends Bootloader
{
    use WireTrait;

    protected const SINGLETONS = [
        EventBusInterface::class => [self::class, 'initEventBus'],
        ListenerRegistryInterface::class => ListenerRegistry::class,
        ListenerProcessorRegistry::class => ListenerProcessorRegistry::class,
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function boot(
        BroadwayConfig $config,
        FactoryInterface $factory,
        ListenerProcessorRegistry $registry,
        AbstractKernel $kernel
    ): void {
        $this->registerEventListeners($config, $registry, $factory, $kernel);
    }

    /**
     * @psalm-param TInterceptor $interceptor
     */
    public function addInterceptor(string|CoreInterceptorInterface|Autowire $interceptor): void
    {
        $this->config->modify(
            BroadwayConfig::CONFIG,
            new Append('domain_interceptors', null, $interceptor)
        );
    }

    private function initEventBus(BroadwayConfig $config, FactoryInterface $factory): EventBus
    {
        $eventBus = new SimpleEventBus();

        $core = new InterceptableCore(new Core($eventBus));

        foreach ($config->getDomainInterceptors() as $interceptor) {
            $interceptor = $this->wire($interceptor, $factory);

            \assert($interceptor instanceof CoreInterceptorInterface);
            $core->addInterceptor($interceptor);
        }

        return new EventBus($core, $eventBus);
    }

    private function registerEventListeners(
        BroadwayConfig $config,
        ListenerProcessorRegistry $registry,
        FactoryInterface $factory,
        AbstractKernel $kernel
    ): void {
        foreach ($config->getProcessors() as $processor) {
            $processor = $this->wire($processor, $factory);

            \assert($processor instanceof ProcessorInterface);
            $registry->addProcessor($processor);
        }

        $kernel->bootstrapped(static function () use ($registry): void {
            $registry->process();
        });
    }
}
