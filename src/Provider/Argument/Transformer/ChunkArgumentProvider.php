<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\Chunk as ChunkTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ChunkArgumentProvider
{
    final public const ARGUMENT_INDEX_LENGTH = 0;
    final public const ARGUMENT_INDEX_PRESERVE_KEYS = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var bool
     */
    private readonly bool $defaultPreserveKeys;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param bool $defaultPreserveKeys
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        bool $defaultPreserveKeys = false,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $this->defaultPreserveKeys = $defaultPreserveKeys;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return positive-int
     */
    public function getLengthArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): int {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_LENGTH,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_numeric($argumentValue) || $argumentValue <= 0) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ChunkTransformer::class,
                errors: [
                    sprintf(
                        'Length argument (%s) must be positive number; Received %s',
                        self::ARGUMENT_INDEX_LENGTH,
                        is_scalar($argumentValue)
                            ? $argumentValue
                            : get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }
        $argumentValue = (int)$argumentValue;

        /** @var int<1, max> $argumentValue */
        return $argumentValue;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     */
    public function getPreserveKeysArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_PRESERVE_KEYS,
            defaultValue: $this->defaultPreserveKeys,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ChunkTransformer::class,
                errors: [
                    sprintf(
                        'Preserve Keys argument (%s) must be null or boolean; Received %s',
                        self::ARGUMENT_INDEX_PRESERVE_KEYS,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return $argumentValue;
    }
}
