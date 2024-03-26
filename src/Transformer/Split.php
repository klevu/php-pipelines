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
use Klevu\Pipelines\Provider\Argument\Transformer\SplitArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to split a string into an array of strings based on passed separator
 *  - <string|Extraction> Separator
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class Split implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_SEPARATOR = SplitArgumentProvider::ARGUMENT_INDEX_SEPARATOR;

    /**
     * @var SplitArgumentProvider
     */
    private readonly SplitArgumentProvider $argumentProvider;

    /**
     * @param SplitArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?SplitArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(SplitArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: SplitArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return array<string|null>|array<array<string|null>>|null
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

        if ($this->shouldCallRecursively($data)) {
            return $this->performRecursiveCall(
                data: (array)$data,
                arguments: $arguments,
                context: $context,
            );
        }

        if (!is_scalar($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'scalar|iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $separatorArgumentValue = $this->argumentProvider->getSeparatorArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return explode(
            separator: $separatorArgumentValue,
            string: (string)$data,
        );
    }
}
