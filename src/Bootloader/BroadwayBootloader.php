<?php

declare(strict_types=1);

namespace Spiral\Broadway\Bootloader;

use Broadway\EventDispatcher\CallableEventDispatcher;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventSourcing\EventStreamDecorator;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnrichingEventStreamDecorator;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Broadway\UuidGenerator\Converter\BinaryUuidConverter;
use Broadway\UuidGenerator\Converter\BinaryUuidConverterInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Broadway\EventHandling\Processor\AttributeProcessor;
use Spiral\Broadway\EventHandling\Processor\ConfigProcessor;
use Spiral\Config\ConfiguratorInterface;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;

final class BroadwayBootloader extends Bootloader
{
    use WireTrait;

    protected const DEPENDENCIES = [
        EventHandlingBootloader::class,
        SerializerBootloader::class,
    ];

    protected const SINGLETONS = [
        EventDispatcher::class => CallableEventDispatcher::class,
        UuidGeneratorInterface::class => Version4Generator::class,
        BinaryUuidConverterInterface::class => BinaryUuidConverter::class,
        EventStreamDecorator::class => MetadataEnrichingEventStreamDecorator::class,
        EventStore::class => [self::class, 'initEventStore'],
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
        $this->bindReadModelRepositoryFactory($config, $container, $factory);
    }

    private function initConfig(): void
    {
        $this->config->setDefaults(
            BroadwayConfig::CONFIG,
            [
                'event_store' => InMemoryEventStore::class,
                'domain_listeners' => [],
                'processors' => [
                    AttributeProcessor::class,
                    ConfigProcessor::class,
                ],
                'read_model_repository_factory' => InMemoryRepositoryFactory::class,
                'payload_serializer' => SimpleInterfaceSerializer::class,
                'read_model_serializer' => SimpleInterfaceSerializer::class,
                'metadata_serializer' => SimpleInterfaceSerializer::class,
                'command_handling_dispatch_events' => false,
            ]
        );
    }

    private function initEventStore(BroadwayConfig $config, Container $container, FactoryInterface $factory): EventStore
    {
        $eventStore = $this->wire($config->getEventStoreImplementation(), $factory);

        \assert($eventStore instanceof EventStore);

        return $eventStore;
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
}
