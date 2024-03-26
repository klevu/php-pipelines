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
use Klevu\Pipelines\Model\Transformation\ChangeCase\Cases;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ChangeCaseArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to change the case of received data
 * Arguments:
 *  - <Cases> Case option to transform string to
 * @see ChangeCaseArgumentProvider
 *
 * @method string|null performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context)
 */
class ChangeCase implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_CASE = ChangeCaseArgumentProvider::ARGUMENT_INDEX_CASE;

    /**
     * @var ChangeCaseArgumentProvider
     */
    private readonly ChangeCaseArgumentProvider $argumentProvider;

    /**
     * @param ChangeCaseArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ChangeCaseArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ChangeCaseArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ChangeCaseArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|array<string|null>|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): null|string|array {
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

        if (!is_string($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'null|string|string[]',
                arguments: $arguments,
                data: $data,
            );
        }

        $caseArgumentValue = $this->argumentProvider->getCaseArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return match ($caseArgumentValue) {
            Cases::LOWERCASE => strtolower($data),
            Cases::UPPERCASE => strtoupper($data),
            Cases::TITLECASE => ucwords($data),
        };
    }
}
