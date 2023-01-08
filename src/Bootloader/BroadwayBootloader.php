<?php

declare(strict_types=1);

namespace Spiral\Broadway\Bootloader;

use Broadway\CommandHandling\CommandBus;
use Broadway\CommandHandling\EventDispatchingCommandBus;
use Broadway\CommandHandling\SimpleCommandBus;
use Broadway\EventDispatcher\CallableEventDispatcher;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventBus;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventSourcing\EventStreamDecorator;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnrichingEventStreamDecorator;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Broadway\UuidGenerator\Converter\BinaryUuidConverter;
use Broadway\UuidGenerator\Converter\BinaryUuidConverterInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\Container\Autowire;
use Spiral\Core\FactoryInterface;

final class BroadwayBootloader extends Bootloader
{
    protected const SINGLETONS = [
        Serializer::class => SimpleInterfaceSerializer::class,
        EventBus::class => SimpleEventBus::class,
        EventDispatcher::class => CallableEventDispatcher::class,
        UuidGeneratorInterface::class => Version4Generator::class,
        BinaryUuidConverterInterface::class => BinaryUuidConverter::class,
        EventStreamDecorator::class => MetadataEnrichingEventStreamDecorator::class,
        EventDispatchingCommandBus::class => [self::class, 'initEventDispatchingCommandBus'],
    ];

    public function __construct(
        private readonly ConfiguratorInterface $config
    ) {
    }

    public function init(): void
    {
        $this->initConfig();
    }

    public function boot(BroadwayConfig $config, Container $container, FactoryInterface $factory): void
    {
        $this->bindCommandBus($config, $container);
        $this->bindEventStore($config, $container, $factory);
        $this->bindReadModelRepositoryFactory($config, $container, $factory);
        $this->bindSerializers($config, $container, $factory);
    }

    private function initConfig(): void
    {
        $this->config->setDefaults(
            BroadwayConfig::CONFIG,
            [
                'event_store' => InMemoryEventStore::class,
                'read_model_repository_factory' => InMemoryRepositoryFactory::class,
                'payload_serializer' => SimpleInterfaceSerializer::class,
                'read_model_serializer' => SimpleInterfaceSerializer::class,
                'metadata_serializer' => SimpleInterfaceSerializer::class,
                'command_handling_dispatch_events' => false,
            ]
        );
    }

    private function bindCommandBus(BroadwayConfig $config, Container $container): void
    {
        $config->isDispatchEvents()
            ? $container->bindSingleton(CommandBus::class, EventDispatchingCommandBus::class)
            : $container->bindSingleton(CommandBus::class, SimpleCommandBus::class);
    }

    private function bindEventStore(BroadwayConfig $config, Container $container, FactoryInterface $factory): void
    {
        $eventStore = $this->wire($config->getEventStoreImplementation(), $factory);

        \assert($eventStore instanceof EventStore);

        $container->bindSingleton(EventStore::class, $eventStore);
    }

    private function bindReadModelRepositoryFactory(
        BroadwayConfig $config,
        Container $container,
        FactoryInterface $factory
    ): void {
        $factory = $this->wire($config->getReadModelRepositoryFactoryImplementation(), $factory);

        \assert($factory instanceof RepositoryFactory);

        $container->bindSingleton(RepositoryFactory::class, $factory);
    }

    private function bindSerializers(BroadwayConfig $config, Container $container, FactoryInterface $factory): void
    {
        $payload = $config->getPayloadSerializerImplementation();
        $readModel = $config->getReadModelSerializerImplementation();
        $metadata = $config->getMetadataSerializerImplementation();

        \assert($payload instanceof Serializer);
        \assert($readModel instanceof Serializer);
        \assert($metadata instanceof Serializer);

        $container->bindSingleton('broadway.payload_serializer', $payload);
        $container->bindSingleton('broadway.read_model_serializer', $readModel);
        $container->bindSingleton('broadway.metadata_serializer', $metadata);
    }

    private function initEventDispatchingCommandBus(EventDispatcher $dispatcher): EventDispatchingCommandBus
    {
        return new EventDispatchingCommandBus(new SimpleCommandBus(), $dispatcher);
    }

    private function wire(mixed $alias, FactoryInterface $factory): mixed
    {
        return match (true) {
            \is_string($alias) => $factory->make($alias),
            $alias instanceof Autowire => $alias->resolve($factory),
            default => $alias
        };
    }
}
