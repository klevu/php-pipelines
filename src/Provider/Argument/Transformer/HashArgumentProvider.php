<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\Hash\Algorithms;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\Hash as HashTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class HashArgumentProvider
{
    final public const ARGUMENT_INDEX_ALGORITHM = 0;
    final public const ARGUMENT_INDEX_SALT = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var Algorithms
     */
    private readonly Algorithms $defaultAlgorithm;
    /**
     * @var string
     */
    private readonly string $defaultSalt;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param Algorithms $defaultAlgorithm
     * @param string $defaultSalt
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        Algorithms $defaultAlgorithm = Algorithms::SHA3_256,
        string $defaultSalt = '',
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

        $this->defaultAlgorithm = $defaultAlgorithm;
        $this->defaultSalt = $defaultSalt;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return Algorithms
     * @throws InvalidTransformationArgumentsException
     */
    public function getAlgorithmArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): Algorithms {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ALGORITHM,
            defaultValue: $this->defaultAlgorithm,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultAlgorithm;
        }

        switch (true) {
            case $argumentValue instanceof Algorithms:
                break;

            case empty($argumentValue):
                throw new InvalidTransformationArgumentsException(
                    transformerName: HashTransformer::class,
                    errors: [
                        sprintf(
                            'Algorithm argument (%s) is required',
                            self::ARGUMENT_INDEX_ALGORITHM,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );

            case is_string($argumentValue):
                try {
                    $argumentValue = Algorithms::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: HashTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Algorithm argument (%s) value: %s',
                                self::ARGUMENT_INDEX_ALGORITHM,
                                $argumentValue,
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
                    transformerName: HashTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Algorithm argument (%s): %s',
                            self::ARGUMENT_INDEX_ALGORITHM,
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
    public function getSaltArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): string {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_SALT,
            defaultValue: $this->defaultSalt,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            $argumentValue = $this->defaultSalt;
        }

        if (!is_scalar($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: HashTransformer::class,
                errors: [
                    sprintf(
                        'Invalid Salt argument (%s): %s',
                        self::ARGUMENT_INDEX_SALT,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return (string)$argumentValue;
    }
}
