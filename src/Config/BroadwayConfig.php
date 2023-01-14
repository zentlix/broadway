<?php

declare(strict_types=1);

namespace Spiral\Broadway\Config;

use Broadway\EventHandling\EventListener;
use Broadway\EventStore\EventStore;
use Broadway\EventStore\InMemoryEventStore;
use Broadway\ReadModel\InMemory\InMemoryRepositoryFactory;
use Broadway\ReadModel\RepositoryFactory;
use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Spiral\Broadway\EventHandling\Processor\ProcessorInterface;
use Spiral\Core\Container\Autowire;
use Spiral\Core\CoreInterceptorInterface;
use Spiral\Core\InjectableConfig;

/**
 * @psalm-type TEventListener = EventListener|class-string<EventListener>|Autowire<EventListener>
 * @psalm-type TInterceptor = class-string<CoreInterceptorInterface>|CoreInterceptorInterface|Autowire<CoreInterceptorInterface>
 * @psalm-type TProcessor = ProcessorInterface|class-string<ProcessorInterface>|Autowire<ProcessorInterface>
 * @psalm-type TEventStore = EventStore|class-string<EventStore>|Autowire<EventStore>
 * @psalm-type TRepositoryFactory = RepositoryFactory|class-string<RepositoryFactory>|Autowire<RepositoryFactory>
 * @psalm-type TSerializer = Serializer|class-string<Serializer>|Autowire<Serializer>
 *
 * @property array{
 *     event_store: TEventStore,
 *     domain_listeners: TEventListener[],
 *     domain_interceptors: TInterceptor[],
 *     processors: TProcessor[],
 *     read_model_repository_factory: TRepositoryFactory,
 *     payload_serializer: TSerializer,
 *     read_model_serializer: TSerializer,
 *     metadata_serializer: TSerializer,
 *     command_handling_dispatch_events: boolean
 * } $config
 */
final class BroadwayConfig extends InjectableConfig
{
    public const CONFIG = 'broadway';

    protected array $config = [
        'event_store' => InMemoryEventStore::class,
        'domain_listeners' => [],
        'domain_interceptors' => [],
        'processors' => [],
        'read_model_repository_factory' => InMemoryRepositoryFactory::class,
        'payload_serializer' => SimpleInterfaceSerializer::class,
        'read_model_serializer' => SimpleInterfaceSerializer::class,
        'metadata_serializer' => SimpleInterfaceSerializer::class,
        'command_handling_dispatch_events' => false,
    ];

    /**
     * @psalm-return TEventListener[]
     */
    public function getDomainListeners(): array
    {
        return $this->config['domain_listeners'] ?? [];
    }

    /**
     * @psalm-return TInterceptor[]
     */
    public function getDomainInterceptors(): array
    {
        return $this->config['domain_interceptors'] ?? [];
    }

    /**
     * @psalm-return TProcessor[]
     */
    public function getProcessors(): array
    {
        return $this->config['processors'] ?? [];
    }

    /**
     * @psalm-return TEventStore
     */
    public function getEventStoreImplementation(): string|EventStore|Autowire
    {
        return $this->config['event_store'] ?? InMemoryEventStore::class;
    }

    /**
     * @psalm-return TRepositoryFactory
     */
    public function getReadModelRepositoryFactoryImplementation(): string|RepositoryFactory|Autowire
    {
        return $this->config['read_model_repository_factory'] ?? InMemoryRepositoryFactory::class;
    }

    /**
     * @psalm-return TSerializer
     */
    public function getPayloadSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['payload_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    /**
     * @psalm-return TSerializer
     */
    public function getReadModelSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['read_model_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    /**
     * @psalm-return TSerializer
     */
    public function getMetadataSerializerImplementation(): string|Serializer|Autowire
    {
        return $this->config['metadata_serializer'] ?? SimpleInterfaceSerializer::class;
    }

    public function isDispatchEvents(): bool
    {
        return $this->config['command_handling_dispatch_events'] ?? false;
    }
}
