<?php

declare(strict_types=1);

namespace Spiral\Broadway\Config;

use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Spiral\Core\Container\Autowire;
use Spiral\Core\InjectableConfig;

final class BroadwayConfig extends InjectableConfig
{
    public const CONFIG = 'broadway';

    protected array $config = [
        'event_store' => InMemoryEventStore::class,
        'read_model_repository_factory' => InMemoryRepositoryFactory::class,
        'payload_serializer' => SimpleInterfaceSerializer::class,
        'read_model_serializer' => SimpleInterfaceSerializer::class,
        'metadata_serializer' => SimpleInterfaceSerializer::class,
        'command_handling_dispatch_events' => false,
    ];

    /**
     * @return class-string|EventStore|Autowire
     */
    public function getEventStoreImplementation(): string|EventStore|Autowire
    {
        return $this->config['event_store'] ?? InMemoryEventStore::class;
    }

    /**
     * @return class-string|RepositoryFactory|Autowire
     */
    public function getReadModelRepositoryFactoryImplementation(): string|RepositoryFactory|Autowire
    {
        return $this->config['read_model_repository_factory'] ?? InMemoryRepositoryFactory::class;
    }

    /**
     * @return class-string|Serializer|Autowire
     */
    public function getPayloadSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['payload_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    /**
     * @return class-string|Serializer|Autowire
     */
    public function getReadModelSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['read_model_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    /**
     * @return class-string|Serializer|Autowire
     */
    public function getMetadataSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['metadata_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    public function isDispatchEvents(): bool
    {
        return (bool) $this->config['command_handling_dispatch_events'];
    }
}
