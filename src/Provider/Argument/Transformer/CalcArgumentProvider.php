<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\Calc as CalcTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CalcArgumentProvider
{
    final public const ARGUMENT_INDEX_OPERATION = 0;
    final public const ARGUMENT_INDEX_VALUE = 1;

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
     * @return Operations
     * @throws InvalidTransformationArgumentsException
     */
    public function getOperationArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): Operations {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_OPERATION,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case null === $argumentValue:
            case '' === $argumentValue:
                throw new InvalidTransformationArgumentsException(
                    transformerName: CalcTransformer::class,
                    errors: [
                        sprintf(
                            'Operation argument (%s) is required',
                            self::ARGUMENT_INDEX_OPERATION,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );

            case $argumentValue instanceof Operations:
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = Operations::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: CalcTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Operation argument (%s) value: %s',
                                self::ARGUMENT_INDEX_OPERATION,
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
                    transformerName: CalcTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Operation argument (%s): %s',
                            self::ARGUMENT_INDEX_OPERATION,
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
     * @return float|int|numeric-string
     * @throws InvalidTransformationArgumentsException
     */
    public function getValueArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): float|int|string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_VALUE,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case null === $argumentValue:
            case '' === $argumentValue:
                throw new InvalidTransformationArgumentsException(
                    transformerName: CalcTransformer::class,
                    errors: [
                        sprintf(
                            'Value argument (%s) is required',
                            self::ARGUMENT_INDEX_VALUE,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );

            case is_int($argumentValue):
            case is_float($argumentValue):
                break;

            case is_string($argumentValue):
                $argumentValue = trim($argumentValue);
                if (!is_numeric($argumentValue)) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: CalcTransformer::class,
                        errors: [
                            sprintf(
                                'Value argument (%s) must be numeric; received "%s"',
                                self::ARGUMENT_INDEX_VALUE,
                                $argumentValue,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                    );
                }
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: CalcTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Value argument (%s): %s',
                            self::ARGUMENT_INDEX_VALUE,
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
