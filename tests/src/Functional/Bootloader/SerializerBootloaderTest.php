<?php

declare(strict_types=1);

namespace Spiral\Broadway\Tests\Functional\Bootloader;

use Broadway\Serializer\Serializer;
use Broadway\Serializer\SimpleInterfaceSerializer;
use Spiral\Broadway\Bootloader\SerializerBootloader;
use Spiral\Broadway\Config\BroadwayConfig;
use Spiral\Broadway\Tests\Functional\TestCase;

final class SerializerBootloaderTest extends TestCase
{
    public function testSerializerShouldBeBoundAsSingleton(): void
    {
        $this->assertContainerBoundAsSingleton(Serializer::class, SimpleInterfaceSerializer::class);
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

            public function deserialize(array $serializedObject): mixed
            {
            }
        };

        $broadway = new SerializerBootloader();
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
