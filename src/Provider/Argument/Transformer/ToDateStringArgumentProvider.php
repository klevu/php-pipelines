<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\ToDateString as ToDateStringTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ToDateStringArgumentProvider
{
    final public const ARGUMENT_INDEX_FORMAT = 0;
    final public const ARGUMENT_INDEX_TO_TIMEZONE = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string|Extraction
     */
    private readonly string|Extraction $defaultFormat;
    /**
     * @var string|Extraction
     */
    private readonly string|Extraction $defaultToTimezone;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string|Extraction $defaultFormat
     * @param string|Extraction $defaultToTimezone
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        string|Extraction $defaultFormat = DATE_ATOM,
        string|Extraction $defaultToTimezone = 'UTC',
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

        $this->defaultFormat = $defaultFormat;
        $this->defaultToTimezone = $defaultToTimezone;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string
     * @throws InvalidTransformationArgumentsException
     */
    public function getFormatArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_FORMAT,
            defaultValue: $this->defaultFormat,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultFormat;
        }

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ToDateStringTransformer::class,
                errors: [
                    sprintf(
                        'Format argument (%s) must be string; Received %s',
                        self::ARGUMENT_INDEX_FORMAT,
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
    public function getToTimezoneArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_TO_TIMEZONE,
            defaultValue: $this->defaultToTimezone,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultToTimezone;
        }

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: ToDateStringTransformer::class,
                errors: [
                    sprintf(
                        'To Timezone argument (%s) must be string or null; Received %s',
                        self::ARGUMENT_INDEX_TO_TIMEZONE,
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
