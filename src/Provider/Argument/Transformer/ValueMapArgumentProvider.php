<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\ValueMapItem;
use Klevu\Pipelines\Model\Transformation\ValueMapItemIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ValueMap\ItemArgumentProvider as ValueMapItemArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\ValueMap as ValueMapTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ValueMapArgumentProvider
{
    final public const ARGUMENT_INDEX_VALUE_MAP = 0;
    final public const ARGUMENT_INDEX_STRICT = 1;
    final public const ARGUMENT_INDEX_CASE_SENSITIVE = 2;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var ValueMapItemArgumentProvider
     */
    private readonly ValueMapItemArgumentProvider $itemArgumentProvider;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;
    /**
     * @var bool
     */
    private readonly bool $defaultStrict;
    /**
     * @var bool
     */
    private readonly bool $defaultCaseSensitive;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param ValueMapItemArgumentProvider|null $itemArgumentProvider
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @param bool $defaultStrict
     * @param bool $defaultCaseSensitive
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?ValueMapItemArgumentProvider $itemArgumentProvider = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
        bool $defaultStrict = true,
        bool $defaultCaseSensitive = true,
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

        $itemArgumentProvider ??= $container->get(ValueMapItemArgumentProvider::class);
        try {
            $this->itemArgumentProvider = $itemArgumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ValueMapItemArgumentProvider::class,
                instance: $itemArgumentProvider,
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

        $this->defaultStrict = $defaultStrict;
        $this->defaultCaseSensitive = $defaultCaseSensitive;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return ValueMapItemIterator
     * @throws InvalidTransformationArgumentsException
     */
    public function getValueMap(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ValueMapItemIterator {
        $valueMapArgumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_VALUE_MAP,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $strictArgumentValue = $this->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $caseSensitiveArgumentValue = $this->getCaseSensitiveArgumentValue(
            arguments: $arguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case $valueMapArgumentValue instanceof ValueMapItemIterator:
                $valueMap = $valueMapArgumentValue;
                break;

            case is_array($valueMapArgumentValue):
                $valueMapArgumentValue = $this->argumentIteratorFactory->create($valueMapArgumentValue);
                // Intentional cascade
            case $valueMapArgumentValue instanceof ArgumentIterator:
                $valueMap = new ValueMapItemIterator(
                    array_map(
                        fn (Argument $argument): ValueMapItem => $this->createValueMapItemFromArgument(
                            argument: $argument,
                            extractionPayload: $extractionPayload,
                            extractionContext: $extractionContext,
                            strict: $strictArgumentValue,
                            caseSensitive: $caseSensitiveArgumentValue,
                        ),
                        $valueMapArgumentValue->toArray(),
                    ),
                );
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: ValueMapTransformer::class,
                    errors: [
                        sprintf(
                            'Value Map argument (%s) must be instance of array|%s|%s; Received %s',
                            self::ARGUMENT_INDEX_VALUE_MAP,
                            ArgumentIterator::class,
                            ValueMapItemIterator::class,
                            get_debug_type($valueMapArgumentValue),
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );
        }

        return $valueMap;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
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

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ValueMapTransformer::class,
                errors: [
                    sprintf(
                        'Strict argument (%s) must be boolean; Received %s',
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

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     */
    public function getCaseSensitiveArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_CASE_SENSITIVE,
            defaultValue: $this->defaultCaseSensitive,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ValueMapTransformer::class,
                errors: [
                    sprintf(
                        'Case Sensitive argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_INDEX_CASE_SENSITIVE,
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
     * @param Argument $argument
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @param bool|null $strict
     * @param bool|null $caseSensitive
     * @return ValueMapItem
     */
    private function createValueMapItemFromArgument(
        Argument $argument,
        mixed $extractionPayload,
        ?\ArrayAccess $extractionContext,
        ?bool $strict,
        ?bool $caseSensitive,
    ): ValueMapItem {
        $key = $argument->getKey();
        $valueMapItemArguments = $argument->getValue();

        if (
            is_array($valueMapItemArguments)
            && array_key_exists(ValueMapItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE, $valueMapItemArguments)
            && array_key_exists(ValueMapItemArgumentProvider::ARGUMENT_INDEX_CONVERTED_VALUE, $valueMapItemArguments)
        ) {
            if (is_bool($valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_STRICT] ?? null)) {
                $strict = $valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_STRICT];
            }
            if (is_bool($valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE] ?? null)) {
                $caseSensitive = $valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE];
            }

            $valueMapItemArguments = $this->argumentIteratorFactory->create([
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE => $valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE], // phpcs:ignore Generic.Files.LineLength.TooLong
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_CONVERTED_VALUE => $valueMapItemArguments[ValueMapItemArgumentProvider::ARGUMENT_INDEX_CONVERTED_VALUE], // phpcs:ignore Generic.Files.LineLength.TooLong
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_STRICT => $strict,
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => $caseSensitive,
            ]);
        } elseif (!($valueMapItemArguments instanceof ArgumentIterator)) {
            $valueMapItemArguments = $this->argumentIteratorFactory->create([
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE => $key,
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_CONVERTED_VALUE => $valueMapItemArguments,
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_STRICT => $strict,
                ValueMapItemArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE => $caseSensitive,
            ]);
        }

        $sourceValueArgumentValue = $this->itemArgumentProvider->getSourceValueArgumentValue(
            arguments: $valueMapItemArguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $convertedValueArgumentValue = $this->itemArgumentProvider->getConvertedValueArgumentValue(
            arguments: $valueMapItemArguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $strictArgumentValue = $this->itemArgumentProvider->getStrictArgumentValue(
            arguments: $valueMapItemArguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );
        $caseSensitiveArgumentValue = $this->itemArgumentProvider->getCaseSensitiveArgumentValue(
            arguments: $valueMapItemArguments,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        return match (true) {
            (null !== $strictArgumentValue && null !== $caseSensitiveArgumentValue) => new ValueMapItem(
                sourceValue: $sourceValueArgumentValue,
                convertedValue: $convertedValueArgumentValue,
                strict: $strictArgumentValue,
                caseSensitive: $caseSensitiveArgumentValue,
            ),
            null !== $strictArgumentValue => new ValueMapItem(
                sourceValue: $sourceValueArgumentValue,
                convertedValue: $convertedValueArgumentValue,
                strict: $strictArgumentValue,
            ),
            null !== $caseSensitiveArgumentValue => new ValueMapItem(
                sourceValue: $sourceValueArgumentValue,
                convertedValue: $convertedValueArgumentValue,
                caseSensitive: $caseSensitiveArgumentValue,
            ),
            default => new ValueMapItem(
                sourceValue: $sourceValueArgumentValue,
                convertedValue: $convertedValueArgumentValue,
            ),
        };
    }
}
