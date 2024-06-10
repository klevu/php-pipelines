<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\HashArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to one-way hash payload
 * Arguments:
 *  - <Algorithms> Algorithm option to use
 *  - <string|null> Salt (prepended to source data)
 * @see HashArgumentProvider
 */
class Hash implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_ALGORITHM = HashArgumentProvider::ARGUMENT_INDEX_ALGORITHM;
    final public const ARGUMENT_INDEX_SALT = HashArgumentProvider::ARGUMENT_INDEX_SALT;

    /**
     * @var HashArgumentProvider
     */
    private readonly HashArgumentProvider $argumentProvider;

    /**
     * @param HashArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?HashArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(HashArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: HashArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|array<string|null>|null
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): string|array|null {
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
                expectedType: 'null|scalar',
                arguments: $arguments,
                data: $data,
            );
        }

        $algorithmArgumentValue = $this->argumentProvider->getAlgorithmArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $saltArgumentValue = $this->argumentProvider->getSaltArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return hash(
            algo: $algorithmArgumentValue->value,
            data: $saltArgumentValue . $data,
        );
    }
}
