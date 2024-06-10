<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\StringPositions;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\Trim as TrimTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class TrimArgumentProvider
{
    final public const ARGUMENT_INDEX_CHARACTERS = 0;
    final public const ARGUMENT_INDEX_POSITION = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string
     */
    private readonly string $defaultCharacters;
    /**
     * @var StringPositions
     */
    private readonly StringPositions $defaultPosition;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string $defaultCharacters
     * @param StringPositions $defaultPosition
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        string $defaultCharacters = " \n\r\t\v\x00",
        StringPositions $defaultPosition = StringPositions::BOTH,
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

        $this->defaultCharacters = $defaultCharacters;
        $this->defaultPosition = $defaultPosition;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string
     * @throws InvalidTransformationArgumentsException
     */
    public function getCharactersArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_CHARACTERS,
            defaultValue: $this->defaultCharacters,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultCharacters;
        }

        if (!is_string($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: TrimTransformer::class,
                errors: [
                    sprintf(
                        'Characters argument (%s) must be null or string; Received %s',
                        self::ARGUMENT_INDEX_CHARACTERS,
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
     * @return StringPositions
     * @throws InvalidTransformationArgumentsException
     */
    public function getPositionArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): StringPositions {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_POSITION,
            defaultValue: $this->defaultPosition,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultPosition;
        }

        switch (true) {
            case $argumentValue instanceof StringPositions:
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = StringPositions::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: TrimTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Position argument (%s) value',
                                self::ARGUMENT_INDEX_POSITION,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                        previous: $exception,
                    );
                }
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: TrimTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Position argument (%s)',
                            self::ARGUMENT_INDEX_POSITION,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );
        }

        return $argumentValue;
    }
}
