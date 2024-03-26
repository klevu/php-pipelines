<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ChunkArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Chunk implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    /**
     * @var ChunkArgumentProvider
     */
    private readonly ChunkArgumentProvider $argumentProvider;

    /**
     * @param ChunkArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ChunkArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ChunkArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ChunkArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): array {
        if (null === $data) {
            return [];
        }

        try {
            $arrayData = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $lengthArgumentValue = $this->argumentProvider->getLengthArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $preserveKeysArgumentValue = $this->argumentProvider->getPreserveKeysArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return array_chunk(
            array: $arrayData,
            length: $lengthArgumentValue,
            preserve_keys: $preserveKeysArgumentValue,
        );
    }
}
