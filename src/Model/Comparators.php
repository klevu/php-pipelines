<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;

enum Comparators: string
{
    use ConvertIterableToArrayTrait;

    case EQUALS = 'eq';
    case NOT_EQUALS = 'neq';
    case GREATER_THAN = 'gt';
    case GREATER_THAN_OR_EQUALS = 'gte';
    case LESS_THAN = 'lt';
    case LESS_THAN_OR_EQUALS = 'lte';
    case IN = 'in';
    case NOT_IN = 'nin';
    case EMPTY = 'empty';
    case NOT_EMPTY = 'nempty';

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    public function compare(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict = false,
    ): bool {
        return match ($this) {
            self::EQUALS => $this->compareEquals($sourceValue, $compareValue, $strict),
            self::NOT_EQUALS => $this->compareNotEquals($sourceValue, $compareValue, $strict),
            self::GREATER_THAN => $this->compareGreaterThan($sourceValue, $compareValue, $strict),
            self::GREATER_THAN_OR_EQUALS => $this->compareGreaterThanOrEquals($sourceValue, $compareValue, $strict),
            self::LESS_THAN => $this->compareLessThan($sourceValue, $compareValue, $strict),
            self::LESS_THAN_OR_EQUALS => $this->compareLessThanOrEquals($sourceValue, $compareValue, $strict),
            self::IN => $this->compareIn($sourceValue, $compareValue, $strict),
            self::NOT_IN => $this->compareNotIn($sourceValue, $compareValue, $strict),
            self::EMPTY => $this->compareEmpty($sourceValue, $compareValue, $strict),
            self::NOT_EMPTY => $this->compareNotEmpty($sourceValue, $compareValue, $strict),
        };
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     */
    private function compareEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict,
    ): bool {
        return $strict
            ? $sourceValue === $compareValue
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator
            : $sourceValue == $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     */
    private function compareNotEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict,
    ): bool {
        return $strict
            ? $sourceValue !== $compareValue
            // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedNotEqualOperator
            : $sourceValue != $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareGreaterThan(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        $sourceValue = $this->prepareScalarArgument(
            value: $sourceValue,
            argName: 'sourceValue',
            allowNull: true,
        );
        $compareValue = $this->prepareScalarArgument(
            value: $compareValue,
            argName: 'compareValue',
            allowNull: true,
        );

        return $sourceValue > $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareGreaterThanOrEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        $sourceValue = $this->prepareScalarArgument(
            value: $sourceValue,
            argName: 'sourceValue',
            allowNull: true,
        );
        $compareValue = $this->prepareScalarArgument(
            value: $compareValue,
            argName: 'compareValue',
            allowNull: true,
        );

        return $sourceValue >= $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareLessThan(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        $sourceValue = $this->prepareScalarArgument(
            value: $sourceValue,
            argName: 'sourceValue',
            allowNull: true,
        );
        $compareValue = $this->prepareScalarArgument(
            value: $compareValue,
            argName: 'compareValue',
            allowNull: true,
        );

        return $sourceValue < $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareLessThanOrEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        $sourceValue = $this->prepareScalarArgument(
            value: $sourceValue,
            argName: 'sourceValue',
            allowNull: true,
        );
        $compareValue = $this->prepareScalarArgument(
            value: $compareValue,
            argName: 'compareValue',
            allowNull: true,
        );

        return $sourceValue <= $compareValue;
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareIn(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict,
    ): bool {
        $compareValue = $this->prepareIterableArgument(
            value: $compareValue,
            argName: 'compareValue',
        );

        return in_array($sourceValue, $compareValue, $strict);
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     * @throws \InvalidArgumentException
     */
    private function compareNotIn(
        mixed $sourceValue,
        mixed $compareValue,
        bool $strict,
    ): bool {
        $compareValue = $this->prepareIterableArgument(
            value: $compareValue,
            argName: 'compareValue',
        );

        return !in_array($sourceValue, $compareValue, $strict);
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     */
    private function compareEmpty(
        mixed $sourceValue,
        mixed $compareValue, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        if (is_iterable($sourceValue)) {
            $sourceValue = $this->prepareIterableArgument($sourceValue);
        }

        return empty($sourceValue);
    }

    /**
     * @param mixed $sourceValue
     * @param mixed $compareValue
     * @param bool $strict
     * @return bool
     */
    private function compareNotEmpty(
        mixed $sourceValue,
        mixed $compareValue, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        bool $strict, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        if (is_iterable($sourceValue)) {
            $sourceValue = $this->prepareIterableArgument($sourceValue);
        }

        return !empty($sourceValue);
    }

    /**
     * @param mixed $value
     * @param string $argName
     * @param bool $allowNull
     * @return string|int|float|bool|null
     * @throws \InvalidArgumentException
     */
    private function prepareScalarArgument(
        mixed $value,
        string $argName = '',
        bool $allowNull = true,
    ): null|string|int|float|bool {
        if (is_scalar($value) || (null === $value && $allowNull)) {
            return $value;
        }

        throw new \InvalidArgumentException(
            sprintf(
                '%s value for %s comparison must be scalar; Received %s',
                $argName,
                $this->value,
                get_debug_type($value),
            ),
        );
    }

    /**
     * @param mixed $value
     * @param string $argName
     * @return mixed[]
     * @throws \InvalidArgumentException
     */
    private function prepareIterableArgument(
        mixed $value,
        string $argName = '',
    ): array {
        try {
            $return = $this->convertIterableToArray($value);
        } catch (\InvalidArgumentException) {
            throw new \InvalidArgumentException(
                sprintf(
                    '%s value for %s comparison must be iterable; Received %s',
                    $argName,
                    $this->value,
                    get_debug_type($value),
                ),
            );
        }

        return $return;
    }
}
