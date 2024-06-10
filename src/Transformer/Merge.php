<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;

class Merge implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): ?array {
        if (null === $data) {
            return null;
        }

        try {
            $arrayData = $this->convertIterableToArrayRecursive($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'iterable[iterable]',
                arguments: $arguments,
                data: $data,
            );
        }

        try {
            $arrayArguments = $this->convertIterableToArrayRecursive($arguments);

            foreach ($arrayArguments as $arrayArgument) {
                if (!is_iterable($arrayArgument)) {
                    throw new \InvalidArgumentException(
                        sprintf('Received item of type %s', get_debug_type($arrayArgument)),
                    );
                }
            }
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidTransformationArgumentsException(
                transformerName: $this::class,
                errors: [
                    sprintf(
                        'Arguments must be array of iterables: %s',
                        $exception->getMessage(),
                    ),
                ],
                arguments: $arguments,
                data: $data,
                previous: $exception,
            );
        }


        /** @var mixed[][] $arrayArguments */
        return array_merge(
            $arrayData,
            ...$arrayArguments,
        );
    }
}
