<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer\ValueMap;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\ValueMap as ValueMapTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ItemArgumentProvider
{
    final public const ARGUMENT_INDEX_SOURCE_VALUE = 'sourceValue';
    final public const ARGUMENT_INDEX_CONVERTED_VALUE = 'convertedValue';
    final public const ARGUMENT_INDEX_STRICT = 'strict';
    final public const ARGUMENT_INDEX_CASE_SENSITIVE = 'caseSensitive';

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
     * @return mixed
     * @throws InvalidTransformationArgumentsException
     */
    public function getConvertedValueArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $extractionContext = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
    ): mixed {
        return $this->argumentProvider->getArgumentValue(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_CONVERTED_VALUE,
            defaultValue: null,
        );
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool|null
     * @throws InvalidTransformationArgumentsException
     */
    public function getStrictArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_STRICT,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null !== $argumentValue && !is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ValueMapTransformer::class,
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

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool|null
     * @throws InvalidTransformationArgumentsException
     */
    public function getCaseSensitiveArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_CASE_SENSITIVE,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null !== $argumentValue && !is_bool($argumentValue)) {
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
}
