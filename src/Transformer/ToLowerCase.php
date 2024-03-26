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
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\ChangeCase\Cases;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ChangeCaseArgumentProvider;
use Klevu\Pipelines\Transformer\ChangeCase as ChangeCaseTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to convert data to lowercase
 *
 * @see ChangeCaseTransformer
 *
 * @method ?string performRecursiveCall(array $data, ?array $arguments, \ArrayAccess<int|string, mixed>|null $context))
 */
class ToLowerCase implements TransformerInterface
{
    /**
     * @var ChangeCaseTransformer
     */
    private readonly ChangeCaseTransformer $changeCaseTransformer;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;

    /**
     * @param ChangeCaseTransformer|null $changeCaseTransformer
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ChangeCaseTransformer $changeCaseTransformer = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
    ) {
        $container = Container::getInstance();

        $changeCaseTransformer ??= $container->get(ChangeCaseTransformer::class);
        try {
            $this->changeCaseTransformer = $changeCaseTransformer; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ChangeCaseTransformer::class,
                instance: $changeCaseTransformer,
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
     * @return array<string|null>|string|null
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null,
    ): array|string|null {
        return $this->changeCaseTransformer->transform(
            data: $data,
            arguments: $this->argumentIteratorFactory->create([
                ChangeCaseArgumentProvider::ARGUMENT_INDEX_CASE => Cases::LOWERCASE,
            ]),
            context: $context,
        );
    }
}
