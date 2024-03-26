<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\Model\Transformation\FilterComparisonIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\FilterCompare\ItemArgumentProvider as FilterCompareItemArgumentProvider; // phpcs:ignore Generic.Files.LineLength.TooLong
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\FilterCompare as FilterCompareTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class FilterCompareArgumentProvider
{
    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var FilterCompareItemArgumentProvider
     */
    private readonly FilterCompareItemArgumentProvider $itemArgumentProvider;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param FilterCompareItemArgumentProvider|null $itemArgumentProvider
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?FilterCompareItemArgumentProvider $itemArgumentProvider = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
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

        $itemArgumentProvider ??= $container->get(FilterCompareItemArgumentProvider::class);
        try {
            $this->itemArgumentProvider = $itemArgumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentProviderInterface::class,
                instance: $argumentProvider,
            );
        }

        $argumentIteratorFactory ??= $container->get(ArgumentIteratorFactory::class);
        try {
            $this->argumentIteratorFactory = $argumentIteratorFactory; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentIteratorFactory::class,
                instance: $argumentIteratorFactory,
            );
        }
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return FilterComparisonIterator
     * @throws InvalidTransformationArgumentsException
     */
    public function getFilterComparisons(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): FilterComparisonIterator {
        if (null === $arguments) {
            throw new InvalidTransformationArgumentsException(
                transformerName: FilterCompareTransformer::class,
                errors: [
                    'Transformation requires at least one configured comparison',
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        $filterComparisons = new FilterComparisonIterator();
        foreach (array_keys($arguments->toArray()) as $argumentKey) {
            $filterComparisons->addItem(
                item: $this->getFilterComparisonByArgumentKey(
                    arguments: $arguments,
                    argumentKey: $argumentKey,
                    extractionPayload: $extractionPayload,
                    extractionContext: $extractionContext,
                ),
            );
        }

        return $filterComparisons;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param int|string $argumentKey
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return FilterComparison
     * @throws InvalidTransformationArgumentsException
     */
    private function getFilterComparisonByArgumentKey(
        ?ArgumentIterator $arguments,
        int|string $argumentKey,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): FilterComparison {
        $argumentValue = $this->argumentProvider->getArgumentValue(
            arguments: $arguments,
            argumentKey: $argumentKey,
            defaultValue: null,
        );

        switch (true) {
            case $argumentValue instanceof FilterComparison:
                $filterComparison = $argumentValue;
                break;

            case is_array($argumentValue):
                $filterArgumentsData = [
                    FilterCompareItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE =>
                        $argumentValue[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE] ?? null,
                    FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARATOR =>
                        $argumentValue[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARATOR] ?? null,
                ];
                // phpcs:disable Generic.Files.LineLength.TooLong
                if (array_key_exists(FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARE_VALUE, $argumentValue)) {
                    $filterArgumentsData[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARE_VALUE] = $argumentValue[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_COMPARE_VALUE];
                }
                if (array_key_exists(FilterCompareItemArgumentProvider::ARGUMENT_INDEX_STRICT, $argumentValue)) {
                    $filterArgumentsData[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_STRICT] = $argumentValue[FilterCompareItemArgumentProvider::ARGUMENT_INDEX_STRICT];
                }
                // phpcs:enable Generic.Files.LineLength.TooLong

                $argumentValue = $this->argumentIteratorFactory->create($filterArgumentsData);
                // Intentional Cascade
            case $argumentValue instanceof ArgumentIterator:
                $filterComparison = $this->createFilterComparison(
                    arguments: $argumentValue,
                    extractionPayload: $extractionPayload,
                    extractionContext: $extractionContext,
                );
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: FilterCompareTransformer::class,
                    errors: [
                        sprintf(
                            'Argument #%s is not a valid FilterComparison',
                            $argumentKey,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );
        }

        return $filterComparison;
    }

    /**
     * @param ArgumentIterator $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return FilterComparison
     */
    private function createFilterComparison(
        ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): FilterComparison {
        $sourceValueArgumentValue = $this->itemArgumentProvider->getSourceValueArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $comparatorArgumentValue = $this->itemArgumentProvider->getComparatorArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $compareValueArgumentValue = $this->itemArgumentProvider->getCompareValueArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $strictArgumentValue = $this->itemArgumentProvider->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        return new FilterComparison(
            sourceValue: $sourceValueArgumentValue,
            comparator: $comparatorArgumentValue,
            compareValue: $compareValueArgumentValue,
            strict: $strictArgumentValue,
        );
    }
}
