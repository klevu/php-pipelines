<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Traits;

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;

trait ConvertIterableToArrayTrait
{
    /**
     * @param mixed $value
     * @return mixed[]
     * @throws \InvalidArgumentException
     */
    private function convertIterableToArray(mixed $value): array
    {
        if (!is_iterable($value)) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Value of type %s is not iterable',
                    get_debug_type($value),
                ),
            );
        }

        switch (true) {
            case is_array($value):
                $return = $value;
                break;

            case $value instanceof ArgumentIterator:
                $valueArray = $value->toArray();
                $return = [];
                /** @var Argument $argument */
                foreach ($valueArray as $argument) {
                    $argumentKey = $argument->getKey();
                    $key = match (true) {
                        is_integer($argumentKey),
                        null === $argumentKey => null,
                        is_scalar($argumentKey) => (string)$argumentKey,
                        default => throw new \InvalidArgumentException(
                            sprintf(
                                'Argument key must be scalar; received %s',
                                get_debug_type($argumentKey),
                            ),
                        ),
                    };

                    $argumentValue = $argument->getValue();
                    if (is_iterable($argumentValue)) {
                        $argumentValue = $this->convertIterableToArray($argumentValue);
                    }

                    if (null !== $key) {
                        $return[$key] = $argumentValue;
                    } else {
                        $return[] = $argumentValue;
                    }
                }
                break;

            case method_exists($value, 'toArray'):
                $return = $value->toArray();
                break;

            default:
                $return = [];
                foreach ($value as $key => $valueItem) {
                    $return[$key] = $valueItem;
                }
                break;
        }

        return $return;
    }

    /**
     * @param mixed $value
     * @return mixed[]
     * @throws \InvalidArgumentException
     */
    private function convertIterableToArrayRecursive(mixed $value): array
    {
        $return = $this->convertIterableToArray($value);
        // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
        array_walk($return, function (mixed &$item): void {
            if (is_iterable($item)) {
                $item = $this->convertIterableToArrayRecursive($item);
            }
        });

        return $return;
    }
}
