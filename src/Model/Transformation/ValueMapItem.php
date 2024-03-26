<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation;

class ValueMapItem
{
    /**
     * @param mixed $sourceValue
     * @param mixed $convertedValue
     * @param bool $strict
     * @param bool $caseSensitive
     */
    public function __construct(
        public readonly mixed $sourceValue,
        public readonly mixed $convertedValue,
        public readonly bool $strict = true,
        public readonly bool $caseSensitive = true,
    ) {
    }

    /**
     * @param mixed $data
     * @return bool
     */
    public function matches(mixed $data): bool
    {
        switch (true) {
            case $data === $this->sourceValue:
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
            case !$this->strict && $data == $this->sourceValue:
                $return = true;
                break;

            case !$this->caseSensitive && is_string($data) && is_string($this->sourceValue):
                $dataLower = strtolower($data);
                $sourceValueLower = strtolower($this->sourceValue);

                $return = ($this->strict)
                    ? $dataLower === $sourceValueLower
                    // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
                    : $dataLower == $sourceValueLower;
                break;

            default:
                $return = false;
                break;
        }

        return $return;
    }
}
