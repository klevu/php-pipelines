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
use Klevu\Pipelines\Transformer\MaxWords as MaxWordsTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MaxWordsArgumentProvider
{
    final public const ARGUMENT_INDEX_MAX_WORDS = 0;
    final public const ARGUMENT_INDEX_TRUNCATION_STRING = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string|null
     */
    private readonly ?string $defaultTruncationString;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string|null $defaultTruncationString
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?string $defaultTruncationString = null,
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

        $this->defaultTruncationString = $defaultTruncationString;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return int
     * @throws InvalidTransformationArgumentsException
     */
    public function getMaxWordsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): int {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_MAX_WORDS,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (
            (!is_int($argumentValue) && !ctype_digit($argumentValue))
            || $argumentValue <= 0
        ) {
            throw new InvalidTransformationArgumentsException(
                transformerName: MaxWordsTransformer::class,
                errors: [
                    sprintf(
                        'Max Words argument (%s) must be a positive whole number; Received %s',
                        self::ARGUMENT_INDEX_MAX_WORDS,
                        is_scalar($argumentValue)
                            ? $argumentValue
                            : get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return (int)$argumentValue;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string|null
     * @throws InvalidTransformationArgumentsException
     */
    public function getTruncationStringArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_TRUNCATION_STRING,
            defaultValue: $this->defaultTruncationString,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null !== $argumentValue && !is_scalar($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: $this::class,
                errors: [
                    sprintf(
                        'Truncation string argument (%s) must be scalar; Received %s',
                        self::ARGUMENT_INDEX_TRUNCATION_STRING,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return (string)$argumentValue;
    }
}
