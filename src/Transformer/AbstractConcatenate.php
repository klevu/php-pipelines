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
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\JoinArgumentProvider;
use Klevu\Pipelines\Transformer\Join as JoinTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Abstract concatenation using Join transformer
 * @see Append
 * @see Prepend
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
abstract class AbstractConcatenate implements TransformerInterface
{
    use RecursiveCallTrait;

    /**
     * @var JoinTransformer
     */
    private readonly JoinTransformer $joinTransformer;

    /**
     * @param string|bool|int|float|null $data
     * @param ArgumentIterator $arguments
     * @return ArgumentIterator
     */
    abstract protected function prepareJoinTransformData(
        null|bool|string|int|float $data,
        ArgumentIterator $arguments,
    ): ArgumentIterator;

    /**
     * @param JoinTransformer|null $joinTransformer
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     */
    public function __construct(
        ?JoinTransformer $joinTransformer = null,
    ) {
        $container = Container::getInstance();

        $joinTransformer ??= $container->get(JoinTransformer::class);
        try {
            $this->joinTransformer = $joinTransformer; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: JoinTransformer::class,
                instance: $joinTransformer,
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

        if (!is_scalar($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'scalar',
                arguments: $arguments,
                data: $data,
            );
        }

        $arguments = $this->prepareArguments($arguments);

        return $this->joinTransformer->transform(
            data: $this->prepareJoinTransformData(
                data: $data,
                arguments: $arguments,
            ),
            arguments: new ArgumentIterator([
                new Argument(
                    value: '',
                    key: JoinArgumentProvider::ARGUMENT_INDEX_SEPARATOR,
                ),
            ]),
            context: $context,
        );
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @return ArgumentIterator
     * @throws InvalidTransformationArgumentsException
     */
    private function prepareArguments(?ArgumentIterator $arguments): ArgumentIterator
    {
        $return = new ArgumentIterator();

        foreach ($arguments ?? [] as $key => $argument) {
            /** @var Argument $argument */
            $valueToJoin = $argument->getValue();

            if (
                null !== $valueToJoin
                && !is_scalar($valueToJoin)
                && !($valueToJoin instanceof Extraction)
            ) {
                throw new InvalidTransformationArgumentsException(
                    transformerName: $this::class,
                    errors: [
                        sprintf(
                            '%s: Value #%d is invalid. Expected null|scalar|%s, received %s',
                            $this::class,
                            $key,
                            Extraction::class,
                            get_debug_type($valueToJoin),
                        ),
                    ],
                    arguments: $arguments,
                );
            }

            $return->addItem(new Argument(
                value: ($valueToJoin instanceof Extraction)
                    ? $valueToJoin
                    : (string)$valueToJoin,
            ));
        }

        return $return;
    }
}
