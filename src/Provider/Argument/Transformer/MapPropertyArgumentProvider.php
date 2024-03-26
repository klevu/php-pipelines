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
use Klevu\Pipelines\Transformer\MapProperty as MapPropertyTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MapPropertyArgumentProvider
{
    final public const ARGUMENT_INDEX_ACCESSOR = 0;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
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
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string
     * @throws InvalidTransformationArgumentsException
     */
    public function getAccessorArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ACCESSOR,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: MapPropertyTransformer::class,
                errors: [
                    sprintf(
                        'Accessor argument (%s) must be string; Received %s',
                        self::ARGUMENT_INDEX_ACCESSOR,
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
