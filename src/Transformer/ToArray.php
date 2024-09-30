<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;

/**
 * Transformer to convert input data to string
 * Receives no arguments
 */
class ToArray implements TransformerInterface
{
    use RecursiveCallTrait;

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): array {
        return match (true) {
            null === $data => [],
            is_scalar($data) => [$data],
            is_iterable($data) => $this->convertIterableToArray($data),
            default => throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'null|scalar|\Traversable',
                arguments: $arguments,
                data: $data,
            ),
        };
    }
}
