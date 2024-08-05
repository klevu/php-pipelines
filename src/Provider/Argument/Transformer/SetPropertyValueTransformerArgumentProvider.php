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
use Klevu\Pipelines\Transformer\SetPropertyValue as SetPropertyValueTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SetPropertyValueTransformerArgumentProvider
{
    final public const ARGUMENT_INDEX_PROPERTY_KEY = 0;
    final public const ARGUMENT_INDEX_PROPERTY_VALUE = 1;
    final public const ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR = 2;
    final public const ARGUMENT_INDEX_ASSOCIATIVE = 3;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string
     */
    private readonly string $defaultPropertyPathSeparator;
    /**
     * @var bool
     */
    private readonly bool $defaultAssociative;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string $defaultPropertyPathSeparator
     * @param bool $defaultAssociative
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        string $defaultPropertyPathSeparator = '.',
        bool $defaultAssociative = true,
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
        $this->defaultAssociative = $defaultAssociative;
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
                transformerName: SetPropertyValueTransformer::class,
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
     * @return mixed
     */
    public function getPropertyValueValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): mixed {
        return $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_PROPERTY_VALUE,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
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
                transformerName: SetPropertyValueTransformer::class,
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

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     *
     * @return bool
     */
    public function getAssociateValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ASSOCIATIVE,
            defaultValue: $this->defaultAssociative,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultAssociative;
        }

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: SetPropertyValueTransformer::class,
                errors: [
                    sprintf(
                        'Associative argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_INDEX_ASSOCIATIVE,
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
