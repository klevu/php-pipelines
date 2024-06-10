<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\FlattenArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to return merge iterable of iterables
 * Receives no arguments
 */
class Flatten implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_RETAIN_KEYS = FlattenArgumentProvider::ARGUMENT_INDEX_RETAIN_KEYS;

    /**
     * @var FlattenArgumentProvider
     */
    private readonly FlattenArgumentProvider $argumentProvider;

    /**
     * @param FlattenArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?FlattenArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(FlattenArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: FlattenArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): ?array {
        if (null === $data) {
            return null;
        }

        try {
            $arrayData = $this->convertIterableToArrayRecursive($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable[iterable]',
                arguments: $arguments,
                data: $data,
            );
        }

        foreach ($arrayData as $index => $arrayDataItem) {
            if (is_array($arrayDataItem)) {
                continue;
            }

            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable[iterable]',
                errors: [
                    sprintf(
                        'All items must be iterable; Received %s at index %s',
                        get_debug_type($arrayDataItem),
                        $index,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $retainKeysArgumentValue = $this->argumentProvider->getRetainKeysArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        if (!$retainKeysArgumentValue) {
            $arrayData = array_map(
                static fn (mixed $childArrayData): array => is_array($childArrayData) // check for phpstan's benefit
                    ? array_values($childArrayData)
                    : [],
                $arrayData,
            );
        }

        /** @var mixed[][] $arrayData */
        return array_merge(
            ...array_values($arrayData),
        );
    }
}
