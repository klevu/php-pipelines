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
use Klevu\Pipelines\Transformer\FormatNumber as FormatNumberTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FormatNumberArgumentProvider
{
    final public const ARGUMENT_INDEX_DECIMALS = 0;
    final public const ARGUMENT_INDEX_DECIMAL_SEPARATOR = 1;
    final public const ARGUMENT_INDEX_THOUSANDS_SEPARATOR = 2;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var int
     */
    private readonly int $defaultDecimals;
    /**
     * @var string
     */
    private readonly string $defaultDecimalSeparator;
    /**
     * @var string
     */
    private readonly string $defaultThousandsSeparator;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param int $defaultDecimals
     * @param string $defaultDecimalSeparator
     * @param string $defaultThousandsSeparator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        int $defaultDecimals = 0,
        string $defaultDecimalSeparator = '.',
        string $defaultThousandsSeparator = ',',
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

        $this->defaultDecimals = $defaultDecimals;
        $this->defaultDecimalSeparator = $defaultDecimalSeparator;
        $this->defaultThousandsSeparator = $defaultThousandsSeparator;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return int
     * @throws InvalidTransformationArgumentsException
     */
    public function getDecimalsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): int {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_DECIMALS,
            defaultValue: $this->defaultDecimals,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultDecimals;
        }

        if (!is_int($argumentValue) && !ctype_digit($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: FormatNumberTransformer::class,
                errors: [
                    sprintf(
                        'Decimals argument (%s) must be integer; Received %s',
                        self::ARGUMENT_INDEX_DECIMALS,
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
     * @return string
     * @throws InvalidTransformationArgumentsException
     */
    public function getDecimalSeparatorArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_DECIMAL_SEPARATOR,
            defaultValue: $this->defaultDecimalSeparator,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultDecimalSeparator;
        }

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: FormatNumberTransformer::class,
                errors: [
                    sprintf(
                        'Decimal separator argument (%s) must be string; Received %s',
                        self::ARGUMENT_INDEX_DECIMAL_SEPARATOR,
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
     * @return string
     * @throws InvalidTransformationArgumentsException
     */
    public function getThousandsSeparatorArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_THOUSANDS_SEPARATOR,
            defaultValue: $this->defaultThousandsSeparator,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultThousandsSeparator;
        }

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: FormatNumberTransformer::class,
                errors: [
                    sprintf(
                        'Thousands separator argument (%s) must be string; Received %s',
                        self::ARGUMENT_INDEX_THOUSANDS_SEPARATOR,
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
