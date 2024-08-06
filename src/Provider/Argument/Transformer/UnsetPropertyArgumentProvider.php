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
use Klevu\Pipelines\Transformer\UnsetProperty as UnsetPropertyTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UnsetPropertyArgumentProvider
{
    final public const ARGUMENT_INDEX_PROPERTY_KEY = 0;
    final public const ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string
     */
    private readonly string $defaultPropertyPathSeparator;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string $defaultPropertyPathSeparator
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        string $defaultPropertyPathSeparator = '.',
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

        $this->defaultPropertyPathSeparator = $defaultPropertyPathSeparator;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     *
     * @return string
     */
    public function getPropertyKeyValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_PROPERTY_KEY,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_string($argumentValue) && !is_int($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: UnsetPropertyTransformer::class,
                errors: [
                    sprintf(
                        'Property name argument (%s) must be string or int; Received %s',
                        self::ARGUMENT_INDEX_PROPERTY_KEY,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return (string)$argumentValue;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     *
     * @return non-empty-string
     */
    public function getPropertyPathSeparatorValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR,
            defaultValue: $this->defaultPropertyPathSeparator,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultPropertyPathSeparator;
        }

        if (!is_string($argumentValue) || '' === $argumentValue) {
            throw new InvalidTransformationArgumentsException(
                transformerName: UnsetPropertyTransformer::class,
                errors: [
                    sprintf(
                        'Property path separator argument (%s) must be non-empty-string; Received %s',
                        self::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR,
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
