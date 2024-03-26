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
use Klevu\Pipelines\Model\Transformation\ValueMapItem;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ValueMap\ItemArgumentProvider as ValueMapItemArgumentProvider;
use Klevu\Pipelines\Provider\Argument\Transformer\ValueMapArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to convert values using an enumerated list of source -> converted values
 * Arguments
 *  - <ValueMapIterator>
 * @see ValueMapArgumentProvider
 * @see ValueMapItemArgumentProvider
 */
class ValueMap implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_VALUE_MAP = ValueMapArgumentProvider::ARGUMENT_INDEX_VALUE_MAP;
    final public const ARGUMENT_INDEX_STRICT = ValueMapArgumentProvider::ARGUMENT_INDEX_STRICT;
    final public const ARGUMENT_INDEX_CASE_SENSITIVE = ValueMapArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE;

    final public const ITEM_ARGUMENT_KEY_SOURCE_VALUE = ValueMapItemArgumentProvider::ARGUMENT_INDEX_SOURCE_VALUE;
    final public const ITEM_ARGUMENT_KEY_CONVERTED_VALUE = ValueMapItemArgumentProvider::ARGUMENT_INDEX_CONVERTED_VALUE;
    final public const ITEM_ARGUMENT_KEY_STRICT = ValueMapItemArgumentProvider::ARGUMENT_INDEX_STRICT;
    final public const ITEM_ARGUMENT_KEY_CASE_SENSITIVE = ValueMapItemArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE;

    /**
     * @var ValueMapArgumentProvider
     */
    private readonly ValueMapArgumentProvider $argumentProvider;

    /**
     * @param ValueMapArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ValueMapArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ValueMapArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ValueMapArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): mixed {
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

        $valueMap = $this->argumentProvider->getValueMap(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $return = $data;
        /** @var ValueMapItem $valueMapItem */
        foreach ($valueMap as $valueMapItem) {
            if ($valueMapItem->matches($data)) {
                $return = $valueMapItem->convertedValue;
                break;
            }
        }

        return $return;
    }
}
