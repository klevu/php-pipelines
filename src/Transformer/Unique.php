<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\UniqueArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to return unique values from an iterable input
 * Arguments
 *  - <bool> Strict
 */
class Unique implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_STRICT = UniqueArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var UniqueArgumentProvider
     */
    private readonly UniqueArgumentProvider $argumentProvider;

    /**
     * @param UniqueArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?UniqueArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(UniqueArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: UniqueArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]|null
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): ?array {
        if (null === $data) {
            return null;
        }

        try {
            $arrayData = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'null|iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $strictArgumentValue = $this->argumentProvider->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return ($strictArgumentValue)
            ? array_unique($arrayData, SORT_REGULAR)
            : array_unique($arrayData, SORT_STRING);
    }
}
