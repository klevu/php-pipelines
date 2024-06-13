<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\MapPropertyArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

/**
 * Transformer to return array of properties from iterable input using Extractor
 * Arguments:
 *  - <string> Accessor
 * @see MapPropertyArgumentProvider
 * @see Extractor
 */
class MapProperty implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_ACCESSOR = MapPropertyArgumentProvider::ARGUMENT_INDEX_ACCESSOR;
    final public const ARGUMENT_INDEX_RETURN_NULL_ON_FAILED_EXTRACTION = MapPropertyArgumentProvider::ARGUMENT_INDEX_RETURN_NULL_ON_FAILED_EXTRACTION; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var MapPropertyArgumentProvider
     */
    private readonly MapPropertyArgumentProvider $argumentProvider;
    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;
    /**
     * @var LoggerInterface|null
     */
    private readonly ?LoggerInterface $logger;

    /**
     * @param MapPropertyArgumentProvider|null $argumentProvider
     * @param Extractor|null $extractor
     * @param LoggerInterface|null $logger
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?MapPropertyArgumentProvider $argumentProvider = null,
        ?Extractor $extractor = null,
        ?LoggerInterface $logger = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(MapPropertyArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: MapPropertyArgumentProvider::class,
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

        try {
            $logger ??= $container->get(LoggerInterface::class);
        } catch (ClassNotFoundException) {
            $logger = null;
        }
        try {
            $this->logger = $logger; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: LoggerInterface::class,
                instance: $logger,
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
        if (null === $data) {
            return null;
        }

        try {
            $arrayData = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $accessorArgumentValue = $this->argumentProvider->getAccessorArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $returnNullOnFailedExtractionArgumentValue = $this->argumentProvider->getReturnNullOnFailedExtractionArgumentValue( // phpcs:ignore Generic.Files.LineLength.TooLong
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $return = [];
        foreach ($arrayData as $itemKey => $item) {
            try {
                $extractedValue = $this->extractor->extract(
                    source: $item,
                    accessor: $accessorArgumentValue,
                    context: $context,
                );
            } catch (ExtractionException $exception) {
                if (!$returnNullOnFailedExtractionArgumentValue) {
                    throw new InvalidInputDataException(
                        transformerName: $this::class,
                        expectedType: 'iterable',
                        errors: [
                            'Extraction error: ' . $exception->getMessage(),
                        ],
                        arguments: $arguments,
                        data: $data,
                        previous: $exception,
                    );
                }

                $this->logger?->debug(
                    message: 'Extraction error: ' . $exception->getMessage(),
                    context: [
                        'transformerName' => $this::class,
                        'arguments' => $arguments,
                        'data' => $data,
                        'previous' => $exception,
                    ],
                );
                $extractedValue = null;
            }

            $return[$itemKey] = $extractedValue;
        }

        return $return;
    }
}
