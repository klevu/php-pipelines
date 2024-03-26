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
 * Transformer to convert input data to float
 * Receives no arguments
 */
class ToInteger implements TransformerInterface
{
    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return int
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): int {
        return match (true) {
            null === $data => 0,
            is_scalar($data) => (int)$data,
            $data instanceof \Stringable => (int)(string)$data,
            default => throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'null|scalar|\Stringable',
                arguments: $arguments,
                data: $data,
            ),
        };
    }
}
