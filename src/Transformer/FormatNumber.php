<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\FormatNumberArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to format a numeric value, including decimal precision; decimal separator; and thousands separator
 * Arguments:
 *  - <int|Extraction> Decimals
 *  - <string|Extraction|null> Decimal Separator
 *  - <string|Extraction|null> Thousands Separator
 * @see FormatNumberArgumentProvider
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class FormatNumber implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_DECIMALS = FormatNumberArgumentProvider::ARGUMENT_INDEX_DECIMALS;
    final public const ARGUMENT_INDEX_DECIMAL_SEPARATOR = FormatNumberArgumentProvider::ARGUMENT_INDEX_DECIMAL_SEPARATOR; // phpcs:ignore Generic.Files.LineLength.TooLong
    final public const ARGUMENT_INDEX_THOUSANDS_SEPARATOR = FormatNumberArgumentProvider::ARGUMENT_INDEX_THOUSANDS_SEPARATOR; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var FormatNumberArgumentProvider
     */
    private readonly FormatNumberArgumentProvider $argumentProvider;

    /**
     * @param FormatNumberArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?FormatNumberArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(FormatNumberArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: FormatNumberArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|array<string|null>|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): null|string|array {
        if (null === $data) {
            return null;
        }

        if ($this->shouldCallRecursively($data)) {
            return $this->performRecursiveCall(
                data: (array)$data,
                arguments: $arguments,
                context: $context,
            );
        }

        if (!is_numeric($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'numeric|iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $decimalsArgumentValue = $this->argumentProvider->getDecimalsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $decimalSeparatorArgumentValue = $this->argumentProvider->getDecimalSeparatorArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $thousandsSeparatorArgumentValue = $this->argumentProvider->getThousandsSeparatorArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return number_format(
            num: (float)$data,
            decimals: $decimalsArgumentValue,
            decimal_separator: $decimalSeparatorArgumentValue,
            thousands_separator: $thousandsSeparatorArgumentValue,
        );
    }
}
