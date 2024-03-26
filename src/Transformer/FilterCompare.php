<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\FilterCompare\ItemArgumentProvider as FilterCompareItemArgumentProvider; // phpcs:ignore Generic.Files.LineLength.TooLong
use Klevu\Pipelines\Provider\Argument\Transformer\FilterCompareArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to remove items from iterable input which do not match the conditions provided
 * Arguments
 *  - <array[]|FilterComparison[]> Conditions
 * @see FilterComparison
 * @see FilterCompareArgumentProvider
 * @see FilterCompareItemArgumentProvider
 */
class FilterCompare implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ITEM_ARGUMENT_KEY_SOURCE_VALUE = FilterCompareItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE;
    final public const ITEM_ARGUMENT_KEY_COMPARATOR = FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARATOR;
    final public const ITEM_ARGUMENT_KEY_COMPARE_VALUE = FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARE_VALUE; // phpcs:ignore Generic.Files.LineLength.TooLong
    final public const ITEM_ARGUMENT_KEY_STRICT = FilterCompareItemArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var FilterCompareArgumentProvider
     */
    private readonly FilterCompareArgumentProvider $argumentProvider;
    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;

    /**
     * @param FilterCompareArgumentProvider|null $argumentProvider
     * @param Extractor|null $extractor
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?FilterCompareArgumentProvider $argumentProvider = null,
        ?Extractor $extractor = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(FilterCompareArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: FilterCompareArgumentProvider::class,
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
     * @return mixed[]|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): ?array {
        $preparedData = $this->prepareData($data);
        if (null === $preparedData) {
            return null;
        }

        $filterComparisons = $this->argumentProvider->getFilterComparisons(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return array_filter(
            $preparedData,
            function (mixed $item) use ($filterComparisons, $context): bool {
                $return = false;
                /** @var FilterComparison $filterComparison */
                foreach ($filterComparisons as $filterComparison) {
                    $sourceValue = $filterComparison->sourceValue;
                    if ($sourceValue instanceof Extraction) {
                        try {
                            $sourceValue = $this->extractor->extract(
                                source: $item,
                                accessor: $sourceValue->accessor,
                                context: $context,
                            );
                        } catch (ExtractionException) {
                            $sourceValue = null;
                        }
                    }

                    $compareValue = $filterComparison->compareValue;
                    if ($compareValue instanceof Extraction) {
                        try {
                            $compareValue = $this->extractor->extract(
                                source: $item,
                                accessor: $compareValue->accessor,
                                context: $context,
                            );
                        } catch (ExtractionException) {
                            $compareValue = null;
                        }
                    }

                    $return = $filterComparison->comparator->compare(
                        sourceValue: $sourceValue,
                        compareValue: $compareValue,
                        strict: $filterComparison->strict,
                    );

                    if ($return) {
                        break;
                    }
                }

                return $return;
            },
        );
    }

    /**
     * @param mixed $data
     * @return mixed[]|null
     * @throws InvalidInputDataException
     */
    private function prepareData(mixed $data): ?array
    {
        if (null === $data) {
            return null;
        }
        try {
            $return = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable',
                data: $data,
            );
        }

        return $return;
    }
}
