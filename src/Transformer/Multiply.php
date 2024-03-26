<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\CalcArgumentProvider;
use Klevu\Pipelines\Transformer\Calc as CalcTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to multiply payload by numeric value
 * Arguments:
 *  - <numeric> Value
 *
 * @see CalcTransformer
 *
 * @method int|float|null performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context)
 */
class Multiply implements TransformerInterface
{
    final public const ARGUMENT_INDEX_VALUE = 0;

    /**
     * @var CalcTransformer
     */
    private readonly CalcTransformer $calcTransformer;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;

    /**
     * @param CalcTransformer|null $calcTransformer
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?CalcTransformer $calcTransformer = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
    ) {
        $container = Container::getInstance();

        $calcTransformer ??= $container->get(CalcTransformer::class);
        try {
            $this->calcTransformer = $calcTransformer; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: CalcTransformer::class,
                instance: $calcTransformer,
            );
        }

        $argumentIteratorFactory ??= $container->get(ArgumentIteratorFactory::class);
        try {
            $this->argumentIteratorFactory = $argumentIteratorFactory; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentIteratorFactory::class,
                instance: $argumentIteratorFactory,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return array<float|int|null>|float|int|null
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): array|float|int|null {
        $valueArgument = $arguments?->getByKey(self::ARGUMENT_INDEX_VALUE);

        return $this->calcTransformer->transform(
            data: $data,
            arguments: $this->argumentIteratorFactory->create([
                CalcArgumentProvider::ARGUMENT_INDEX_OPERATION => Operations::MULTIPLY,
                CalcArgumentProvider::ARGUMENT_INDEX_VALUE => $valueArgument?->getValue(),
            ]),
            context: $context,
        );
    }
}
