<?php

declare(strict_types=1);

namespace Spiral\Broadway\Bootloader;

use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Spiral\Boot\Bootloader\Bootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Core\Container;
use Spiral\Core\FactoryInterface;

final class SerializerBootloader extends Bootloader
{
    use WireTrait;

    protected const SINGLETONS = [
        Serializer::class => SimpleInterfaceSerializer::class,
    ];

    public function boot(BroadwayConfig $config, Container $container, FactoryInterface $factory): void
    {
        $payload = $this->wire($config->getPayloadSerializerImplementation(), $factory);
        $readModel = $this->wire($config->getReadModelSerializerImplementation(), $factory);
        $metadata = $this->wire($config->getMetadataSerializerImplementation(), $factory);

        \assert($payload instanceof Serializer);
        \assert($readModel instanceof Serializer);
        \assert($metadata instanceof Serializer);

        $container->bindSingleton('broadway.payload_serializer', $payload);
        $container->bindSingleton('broadway.read_model_serializer', $readModel);
        $container->bindSingleton('broadway.metadata_serializer', $metadata);
    }
}
