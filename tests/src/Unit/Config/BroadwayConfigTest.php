<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\Unit\Config;

use Broadway\Domain\DomainEventStream;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\Repository;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use PHPUnit\Framework\TestCase;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Core\Container\Autowire;

final class BroadwayConfigTest extends TestCase
{
    /**
     * @dataProvider eventStoreDataProvider
     */
    public function testGetEventStoreImplementation(mixed $eventStore, mixed $expected): void
    {
        $config = new BroadwayConfig(['event_store' => $eventStore]);

        $this->assertEquals($config->getEventStoreImplementation(), $expected);
    }

    /**
     * @dataProvider readModelRepositoryFactoryDataProvider
     */
    public function testGetReadModelRepositoryFactoryImplementation(mixed $factory, mixed $expected): void
    {
        $config = new BroadwayConfig(['read_model_repository_factory' => $factory]);

        $this->assertEquals($config->getReadModelRepositoryFactoryImplementation(), $expected);
    }

    /**
     * @dataProvider serializerDataProvider
     */
    public function testGetPayloadSerializerImplementation(mixed $serializer, mixed $expected): void
    {
        $config = new BroadwayConfig(['payload_serializer' => $serializer]);

        $this->assertEquals($config->getPayloadSerializerImplementation(), $expected);
    }

    /**
     * @dataProvider serializerDataProvider
     */
    public function testGetReadModelSerializerImplementation(mixed $serializer, mixed $expected): void
    {
        $config = new BroadwayConfig(['read_model_serializer' => $serializer]);

        $this->assertEquals($config->getReadModelSerializerImplementation(), $expected);
    }

    /**
     * @dataProvider serializerDataProvider
     */
    public function testGetMetadataSerializerImplementation(mixed $serializer, mixed $expected): void
    {
        $config = new BroadwayConfig(['metadata_serializer' => $serializer]);

        $this->assertEquals($config->getMetadataSerializerImplementation(), $expected);
    }

    public function testIsDispatchEvents(): void
    {
        $config = new BroadwayConfig(['command_handling_dispatch_events' => true]);
        $this->assertTrue($config->isDispatchEvents());

        $config = new BroadwayConfig(['command_handling_dispatch_events' => false]);
        $this->assertFalse($config->isDispatchEvents());
    }

    public function eventStoreDataProvider(): \Traversable
    {
        $custom = new class implements EventStore {
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

        yield [new InMemoryEventStore(), new InMemoryEventStore()];
        yield [$custom, $custom];
        yield [new Autowire(''), new Autowire('')];
        yield [InMemoryEventStore::class, InMemoryEventStore::class];
    }

    public function readModelRepositoryFactoryDataProvider(): \Traversable
    {
        $custom = new class implements RepositoryFactory {
            public function create(string $name, string $class): Repository
            {
            }
        };

        yield [new InMemoryRepositoryFactory(), new InMemoryRepositoryFactory()];
        yield [$custom, $custom];
        yield [new Autowire(''), new Autowire('')];
        yield [InMemoryRepositoryFactory::class, InMemoryRepositoryFactory::class];
    }

    public function serializerDataProvider(): \Traversable
    {
        $custom = new class implements Serializer {
            public function serialize($object): array
            {
            }

            public function deserialize(array $serializedObject): mixed
            {
            }
        };

        yield [new SimpleInterfaceSerializer(), new SimpleInterfaceSerializer()];
        yield [$custom, $custom];
        yield [new Autowire(''), new Autowire('')];
        yield [SimpleInterfaceSerializer::class, SimpleInterfaceSerializer::class];
    }
}
