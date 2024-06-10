<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer\FilterCompare;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\FilterCompare as FilterCompareTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemArgumentProvider
{
    final public const ARGUMENT_INDEX_SOURCE_VALUE = 0;
    final public const ARGUMENT_INDEX_COMPARATOR = 1;
    final public const ARGUMENT_INDEX_COMPARE_VALUE = 2;
    final public const ARGUMENT_INDEX_STRICT = 3;

    /**
     * @var bool
     */
    private readonly bool $defaultStrict;
    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param bool $defaultStrict
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        bool $defaultStrict = false,
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

        $this->defaultStrict = $defaultStrict;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return mixed
     * @throws InvalidTransformationArgumentsException
     */
    public function getSourceValueArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $extractionContext = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
    ): mixed {
        return $this->argumentProvider->getArgumentValue(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_SOURCE_VALUE,
            defaultValue: null,
        );
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return Comparators
     * @throws InvalidTransformationArgumentsException
     */
    public function getComparatorArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): Comparators {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_COMPARATOR,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case empty($argumentValue):
                throw new InvalidTransformationArgumentsException(
                    transformerName: FilterCompareTransformer::class,
                    errors: [
                        sprintf(
                            'Comparator argument (%s) is required',
                            self::ARGUMENT_INDEX_COMPARATOR,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );

            case $argumentValue instanceof Comparators:
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = Comparators::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: FilterCompareTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Comparator argument (%s) value: %s',
                                self::ARGUMENT_INDEX_COMPARATOR,
                                $argumentValue,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                        previous: $exception,
                    );
                }
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: FilterCompareTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Comparator argument (%s)',
                            self::ARGUMENT_INDEX_COMPARATOR,
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
     * @return mixed
     * @throws InvalidTransformationArgumentsException
     */
    public function getCompareValueArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $extractionContext = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
    ): mixed {
        return $this->argumentProvider->getArgumentValue(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_COMPARE_VALUE,
            defaultValue: null,
        );
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     * @throws InvalidTransformationArgumentsException
     */
    public function getStrictArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_STRICT,
            defaultValue: $this->defaultStrict,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultStrict;
        }

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: FilterCompareTransformer::class,
                errors: [
                    sprintf(
                        'Strict argument (%s) must be null or boolean; Received %s',
                        self::ARGUMENT_INDEX_STRICT,
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
