<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\CalcArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to perform arithmetic operations
 * Arguments:
 *  - <Operations> Operation to perform ("+", "-", "*", "/", "^")
 *  - <numeric> Value for operation
 * @see CalcArgumentProvider
 *
 * @method int|float|null performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context)
 */
class Calc implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_OPERATION = CalcArgumentProvider::ARGUMENT_INDEX_OPERATION;
    final public const ARGUMENT_INDEX_VALUE = CalcArgumentProvider::ARGUMENT_INDEX_VALUE;

    /**
     * @var CalcArgumentProvider
     */
    private readonly CalcArgumentProvider $argumentProvider;

    /**
     * @param CalcArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?CalcArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(CalcArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: CalcArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return array<float|int|null>|float|int|null
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): array|float|int|null {
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

        if (!is_numeric($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'numeric|numeric[]',
                arguments: $arguments,
                data: $data,
            );
        }

        $operationArgumentValue = $this->argumentProvider->getOperationArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $valueArgumentValue = $this->argumentProvider->getValueArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        if (Operations::DIVIDE === $operationArgumentValue && !$valueArgumentValue) {
            throw new InvalidTransformationArgumentsException(
                transformerName: $this::class,
                errors: [
                    sprintf(
                        'Value argument (%s) must not be zero for division operations',
                        self::ARGUMENT_INDEX_VALUE,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        return match ($operationArgumentValue) {
            Operations::ADD => $data + $valueArgumentValue,
            Operations::SUBTRACT => $data - $valueArgumentValue,
            Operations::MULTIPLY => $data * $valueArgumentValue,
            Operations::DIVIDE => $data / $valueArgumentValue,
            Operations::POW => pow(
                num: (float)$data,
                exponent: (float)$valueArgumentValue,
            ),
        };
    }
}
