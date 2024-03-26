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
use Klevu\Pipelines\Provider\Argument\Transformer\ToDateStringArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to convert a date string to a different format
 * Arguments:
 *  - <string> Format to return data in. Default "c"
 * @see ToDateStringArgumentProvider
 *
 * @link https://www.php.net/manual/en/datetimeimmutable.createfromformat.php
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class ToDateString implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_FORMAT = ToDateStringArgumentProvider::ARGUMENT_INDEX_FORMAT;
    final public const ARGUMENT_INDEX_TO_TIMEZONE = ToDateStringArgumentProvider::ARGUMENT_INDEX_TO_TIMEZONE;

    /**
     * @var ToDateStringArgumentProvider
     */
    private readonly ToDateStringArgumentProvider $argumentProvider;

    /**
     * @param ToDateStringArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ToDateStringArgumentProvider $argumentProvider,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ToDateStringArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ToDateStringArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return array<string|null>|string|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): array|string|null {
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

        if (!is_int($data) && !is_string($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'int|string|int[]|string[]',
                arguments: $arguments,
                data: $data,
            );
        }

        $timestamp = is_int($data) ? $data : strtotime(
            datetime: $data,
            baseTimestamp: time(),
        );
        if (false === $timestamp) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'int|string|int[]|string[]',
                arguments: $arguments,
                data: $data,
                message: 'Input data cannot be converted to date time',
            );
        }

        $formatArgumentValue = $this->argumentProvider->getFormatArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $toTimezoneArgumentValue = $this->argumentProvider->getToTimezoneArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        try {
            $dateTime = new \DateTime(
                datetime: 'now',
                timezone: new \DateTimeZone($toTimezoneArgumentValue),
            );
            $dateTime->setTimestamp(
                timestamp: $timestamp,
            );
        } catch (\Exception $exception) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'int|string|int[]|string[]',
                arguments: $arguments,
                data: $data,
                message: 'Input data cannot be converted to date time',
                previous: $exception,
            );
        }

        return $dateTime->format($formatArgumentValue);
    }
}
