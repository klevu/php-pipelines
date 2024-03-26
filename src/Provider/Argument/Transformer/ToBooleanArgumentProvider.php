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
use Klevu\Pipelines\Transformer\ToBoolean as ToBooleanTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ToBooleanArgumentProvider
{
    final public const ARGUMENT_INDEX_CASE_SENSITIVE = 0;
    final public const ARGUMENT_INDEX_TRIM_WHITESPACE = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var bool
     */
    private readonly bool $defaultCaseSensitive;
    /**
     * @var bool
     */
    private readonly bool $defaultTrimWhitespace;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param bool $defaultCaseSensitive
     * @param bool $defaultTrimWhitespace
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        bool $defaultCaseSensitive = true,
        bool $defaultTrimWhitespace = false,
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

        $this->defaultCaseSensitive = $defaultCaseSensitive;
        $this->defaultTrimWhitespace = $defaultTrimWhitespace;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     * @throws InvalidTransformationArgumentsException
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
                transformerName: ToBooleanTransformer::class,
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
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     * @throws InvalidTransformationArgumentsException
     */
    public function getTrimWhitespaceArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_TRIM_WHITESPACE,
            defaultValue: $this->defaultTrimWhitespace,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ToBooleanTransformer::class,
                errors: [
                    sprintf(
                        'Trim Whitespace argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_INDEX_TRIM_WHITESPACE,
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
