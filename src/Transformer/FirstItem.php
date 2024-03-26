<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;

/**
 * Transformer to return first item from iterable input data
 * Receives no arguments
 */
class FirstItem implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): mixed {
        if (null === $data || is_scalar($data)) {
            return $data;
        }

        try {
            $arrayData = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        if (!count($arrayData)) {
            return null;
        }

        [$firstItem] = array_values($arrayData);

        return $firstItem;
    }
}
