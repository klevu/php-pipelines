<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\ChangeCase\Cases;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\ChangeCase as ChangeCaseTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ChangeCaseArgumentProvider
{
    final public const ARGUMENT_INDEX_CASE = 0;

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
     * @return Cases
     * @throws InvalidTransformationArgumentsException
     */
    public function getCaseArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): Cases {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_CASE,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case empty($argumentValue):
                throw new InvalidTransformationArgumentsException(
                    transformerName: ChangeCaseTransformer::class,
                    errors: [
                        sprintf(
                            'Case argument (%s) is required',
                            self::ARGUMENT_INDEX_CASE,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );

            case $argumentValue instanceof Cases:
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = Cases::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: ChangeCaseTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Case argument (%s) value: %s',
                                self::ARGUMENT_INDEX_CASE,
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
                    transformerName: ChangeCaseTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Case argument (%s)',
                            self::ARGUMENT_INDEX_CASE,
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );
        }

        return $argumentValue;
    }
}
