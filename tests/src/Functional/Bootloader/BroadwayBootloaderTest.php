<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\Functional\Bootloader;

use Broadway\CommandHandling\CommandBus;
use Broadway\CommandHandling\EventDispatchingCommandBus;
use Broadway\CommandHandling\SimpleCommandBus;
use Broadway\Domain\DomainEventStream;
use Broadway\EventDispatcher\CallableEventDispatcher;
use Broadway\EventDispatcher\EventDispatcher;
use Broadway\EventHandling\EventBus;
use Broadway\EventHandling\SimpleEventBus;
use Broadway\EventSourcing\EventStreamDecorator;
use Broadway\EventSourcing\MetadataEnrichment\MetadataEnrichingEventStreamDecorator;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\Repository;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Broadway\UuidGenerator\Converter\BinaryUuidConverter;
use Broadway\UuidGenerator\Converter\BinaryUuidConverterInterface;
use Broadway\UuidGenerator\Rfc4122\Version4Generator;
use Broadway\UuidGenerator\UuidGeneratorInterface;
use Spiral\Broadway\Bootloader\BroadwayBootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Broadway\Tests\Functional\TestCase;
use Spiral\Config\ConfiguratorInterface;

final class BroadwayBootloaderTest extends TestCase
{
    public function testSerializerShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(Serializer::class, SimpleInterfaceSerializer::class);
    }

    public function testEventBusShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(EventBus::class, SimpleEventBus::class);
    }

    public function testEventDispatcherShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(EventDispatcher::class, CallableEventDispatcher::class);
    }

    public function testUuidGeneratorShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(UuidGeneratorInterface::class, Version4Generator::class);
    }

    public function testBinaryUuidConverterShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(BinaryUuidConverterInterface::class, BinaryUuidConverter::class);
    }

    public function testEventStreamDecoratorShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(
            EventStreamDecorator::class,
            MetadataEnrichingEventStreamDecorator::class
        );
    }

    public function testEventDispatchingCommandBusShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(
            EventDispatchingCommandBus::class,
            EventDispatchingCommandBus::class
        );
    }

    public function testDefaultConfigShouldBeDefined(): void
    {
        $this->assertConfigMatches(BroadwayConfig::CONFIG, [
            'event_store' => InMemoryEventStore::class,
            'read_model_repository_factory' => InMemoryRepositoryFactory::class,
            'payload_serializer' => SimpleInterfaceSerializer::class,
            'read_model_serializer' => SimpleInterfaceSerializer::class,
            'metadata_serializer' => SimpleInterfaceSerializer::class,
            'command_handling_dispatch_events' => false,
        ]);
    }

    public function testDefaultCommandBus(): void
    {
        $this->assertContainerBoundAsSingleton(CommandBus::class, SimpleCommandBus::class);
    }

    public function testEventDispatchingCommandBus(): void
    {
        $broadway = new BroadwayBootloader($this->createMock(ConfiguratorInterface::class));
        $broadway->boot(new BroadwayConfig([
            'command_handling_dispatch_events' => true,
        ]), $this->getContainer(), $this->getContainer());

        $this->assertContainerBoundAsSingleton(CommandBus::class, EventDispatchingCommandBus::class);
    }

    public function testDefaultEventStore(): void
    {
        $this->assertContainerBoundAsSingleton(EventStore::class, InMemoryEventStore::class);
    }

    public function testCustomEventStore(): void
    {
        $eventStore = new class implements EventStore {
            public function load(mixed $id): DomainEventStream
            {
            }

            public function loadFromPlayhead(mixed $id, int $playhead): DomainEventStream
            {
            }

            public function append(mixed $id, DomainEventStream $eventStream): void
            {
            }
        };

        $broadway = new BroadwayBootloader($this->createMock(ConfiguratorInterface::class));
        $broadway->boot(new BroadwayConfig([
            'event_store' => $eventStore::class,
        ]), $this->getContainer(), $this->getContainer());

        $this->assertContainerBoundAsSingleton(EventStore::class, $eventStore::class);
    }

    public function testDefaultReadModelRepositoryFactory(): void
    {
        $this->assertContainerBoundAsSingleton(RepositoryFactory::class, InMemoryRepositoryFactory::class);
    }

    public function testCustomReadModelRepositoryFactory(): void
    {
        $factory = new class implements RepositoryFactory {
            public function create(string $name, string $class): Repository
            {
            }
        };

        $broadway = new BroadwayBootloader($this->createMock(ConfiguratorInterface::class));
        $broadway->boot(new BroadwayConfig([
            'read_model_repository_factory' => $factory::class,
        ]), $this->getContainer(), $this->getContainer());

        $this->assertContainerBoundAsSingleton(RepositoryFactory::class, $factory::class);
    }

    public function testDefaultSerializers(): void
    {
        $this->assertContainerBoundAsSingleton('broadway.payload_serializer', SimpleInterfaceSerializer::class);
        $this->assertContainerBoundAsSingleton('broadway.read_model_serializer', SimpleInterfaceSerializer::class);
        $this->assertContainerBoundAsSingleton('broadway.metadata_serializer', SimpleInterfaceSerializer::class);
    }

    public function testCustomSerializers(): void
    {
        $serializer = new class implements Serializer {
            public function serialize($object): array
            {
            }

            public function deserialize(array $serializedObject): void
            {
            }
        };

        $broadway = new BroadwayBootloader($this->createMock(ConfiguratorInterface::class));
        $broadway->boot(new BroadwayConfig([
            'payload_serializer' => $serializer::class,
            'read_model_serializer' => $serializer::class,
            'metadata_serializer' => $serializer::class,
        ]), $this->getContainer(), $this->getContainer());

        $this->assertContainerBoundAsSingleton('broadway.payload_serializer', $serializer::class);
        $this->assertContainerBoundAsSingleton('broadway.read_model_serializer', $serializer::class);
        $this->assertContainerBoundAsSingleton('broadway.metadata_serializer', $serializer::class);
    }
}
