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
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\JoinArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to join iterable input data to a string
 * Arguments:
 *  - <string|Extraction> Separator
 * @see JoinArgumentProvider
 */
class Join implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_SEPARATOR = JoinArgumentProvider::ARGUMENT_INDEX_SEPARATOR;

    /**
     * @var JoinArgumentProvider
     */
    private readonly JoinArgumentProvider $argumentProvider;
    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;

    /**
     * @param JoinArgumentProvider|null $argumentProvider
     * @param Extractor|null $extractor
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?JoinArgumentProvider $argumentProvider = null,
        ?Extractor $extractor = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(JoinArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: JoinArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $extractor ??= $container->get(Extractor::class);
        try {
            $this->extractor = $extractor; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: Extractor::class,
                instance: $extractor,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): ?string {
        if (null === $data) {
            return null;
        }

        try {
            $arrayData = $this->convertIterableToArray($data);
            array_walk(
                $arrayData,
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                function (mixed &$item) use ($data, $context): void {
                    if ($item instanceof Argument) {
                        $item = $item->getValue();
                    }

                    if ($item instanceof Extraction) {
                        $item = $this->extractor->extract(
                            source: $data,
                            accessor: $item->accessor,
                            context: $context,
                        );
                    }

                    if (!is_scalar($item)) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'Data items must be, or evaluate, to a scalar value. Received %s',
                                get_debug_type($item),
                            ),
                        );
                    } elseif (!is_string($item)) {
                        $item = (string)$item;
                    }
                },
            );
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable',
                arguments: $arguments,
                data: $data,
                previous: $exception,
            );
        }

        $separatorArgumentValue = $this->argumentProvider->getSeparatorArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return implode(
            separator: $separatorArgumentValue,
            array: $arrayData,
        );
    }
}
