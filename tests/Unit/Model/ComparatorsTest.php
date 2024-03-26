<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Model;

use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\IteratorInterface;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Comparators::class)]
class ComparatorsTest extends TestCase
{
    #[Test]
    // String
    #[TestWith(['1', '1', true])]
    #[TestWith(['1', 1, true])]
    #[TestWith(['', ' ', false])]
    // Int
    #[TestWith([0, 0, true])]
    #[TestWith([0, false, true])]
    #[TestWith([1, 1, true])]
    #[TestWith([1, '1', true])]
    // Float
    #[TestWith([0.0, 0.0, true])]
    #[TestWith([0.0, null, true])]
    #[TestWith([1.0, 1, true])]
    #[TestWith([1.0, '1', true])]
    #[TestWith([3.14, 3.14 + PHP_FLOAT_EPSILON, false])]
    #[TestWith([3.14, 3.14 + (PHP_FLOAT_EPSILON / 10), true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, '1', true])]
    #[TestWith([true, 1, true])]
    #[TestWith([false, false, true])]
    #[TestWith([false, null, true])]
    #[TestWith([false, 0, true])]
    #[TestWith([true, false, false])]
    // Null
    #[TestWith([null, null, true])]
    #[TestWith([null, '', true])]
    #[TestWith([null, 0, true])]
    #[TestWith([null, false, true])]
    #[TestWith([null, [], true])]
    // Array
    #[TestWith([[], [], true])]
    #[TestWith([['foo' => 'bar'], ['foo' => 'bar'], true])]
    #[TestWith([['a' => 'b', 'c' => 'd'], ['c' => 'd', 'a' => 'b'], true])]
    #[TestWith([[1, 2, 3], [1 => 1, 2, 3], false])]
    #[TestWith([[0 => 0, 1 => 1], [1 => 1, 0 => 0], true])]
    #[TestWith([['foo' => ['bar' => 'baz']], ['foo' => ['bar' => 'baz']], true])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass(), true])]
    public function testCompare_Equals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['1', '1', true])]
    #[TestWith(['1', 1, false])] // Strict
    #[TestWith(['', ' ', false])]
    // Int
    #[TestWith([0, 0, true])]
    #[TestWith([0, false, false])] // Strict
    #[TestWith([1, 1, true])]
    #[TestWith([1, '1', false])] // Strict
    // Float
    #[TestWith([0.0, 0.0, true])]
    #[TestWith([0.0, null, false])]
    #[TestWith([1.0, 1, false])]
    #[TestWith([1.0, '1', false])]
    #[TestWith([3.14, 3.14 + PHP_FLOAT_EPSILON, false])]
    #[TestWith([3.14, 3.14 + (PHP_FLOAT_EPSILON / 10), true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, '1', false])]
    #[TestWith([true, 1, false])]
    #[TestWith([false, false, true])]
    #[TestWith([false, null, false])]
    #[TestWith([false, 0, false])]
    #[TestWith([true, false, false])]
    // Null
    #[TestWith([null, null, true])]
    #[TestWith([null, '', false])]
    #[TestWith([null, 0, false])]
    #[TestWith([null, false, false])]
    #[TestWith([null, [], false])]
    // Array
    #[TestWith([[], [], true])]
    #[TestWith([['foo' => 'bar'], ['foo' => 'bar'], true])]
    #[TestWith([['a' => 'b', 'c' => 'd'], ['c' => 'd', 'a' => 'b'], false])]
    #[TestWith([[1, 2, 3], [1 => 1, 2, 3], false])]
    #[TestWith([[0 => 0, 1 => 1], [1 => 1, 0 => 0], false])]
    #[TestWith([['foo' => ['bar' => 'baz']], ['foo' => ['bar' => 'baz']], true])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass(), false])] // Different object hash
    public function testCompare_Equals_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', false])]
    #[TestWith(['1', ' ', true])]
    #[TestWith(['1', '1', false])]
    #[TestWith(['2', '1', true])]
    #[TestWith(['1', 1, false])]
    #[TestWith(['2', 1, true])]
    #[TestWith(['1', 1.1, false])]
    #[TestWith(['2', 1.1, true])]
    #[TestWith(['2', null, true])]
    #[TestWith(['2', true, false])]
    // Int
    #[TestWith([1, ' ', true])]
    #[TestWith([1, '1', false])]
    #[TestWith([2, '1', true])]
    #[TestWith([1, 1, false])]
    #[TestWith([2, 1, true])]
    #[TestWith([1, 1.1, false])]
    #[TestWith([2, 1.1, true])]
    #[TestWith([2, null, true])]
    #[TestWith([2, true, false])] // PHP type juggling
    // Float
    #[TestWith([1.0, ' ', true])]
    #[TestWith([1.0, '1', false])]
    #[TestWith([2.0, '1', true])]
    #[TestWith([1.0, 1, false])]
    #[TestWith([2.0, 1, true])]
    #[TestWith([1.0, 1.1, false])]
    #[TestWith([2.0, 1.1, true])]
    #[TestWith([2.0, null, true])]
    #[TestWith([2.0, true, false])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, true])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, false])]
    // Bool
    #[TestWith([true, true, false])]
    #[TestWith([true, false, true])]
    #[TestWith([true, '1', false])]
    #[TestWith([true, '0', true])]
    #[TestWith([true, 1, false])]
    #[TestWith([true, 0, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, false, false])]
    #[TestWith([false, '1', false])]
    #[TestWith([false, '0', false])]
    #[TestWith([false, 1, false])]
    #[TestWith([false, 0, false])]
    // Null
    #[TestWith([null, true, false])]
    #[TestWith([null, false, false])]
    #[TestWith([null, '1', false])]
    #[TestWith([null, '0', false])]
    #[TestWith([null, 1, false])]
    #[TestWith([null, 0, false])]
    public function testCompare_GreaterThan(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::GREATER_THAN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', false])]
    #[TestWith(['1', ' ', true])]
    #[TestWith(['1', '1', false])]
    #[TestWith(['2', '1', true])]
    #[TestWith(['1', 1, false])]
    #[TestWith(['2', 1, true])]
    #[TestWith(['1', 1.1, false])]
    #[TestWith(['2', 1.1, true])]
    #[TestWith(['2', null, true])]
    #[TestWith(['2', true, false])]
    // Int
    #[TestWith([1, ' ', true])]
    #[TestWith([1, '1', false])]
    #[TestWith([2, '1', true])]
    #[TestWith([1, 1, false])]
    #[TestWith([2, 1, true])]
    #[TestWith([1, 1.1, false])]
    #[TestWith([2, 1.1, true])]
    #[TestWith([2, null, true])]
    #[TestWith([2, true, false])] // PHP type juggling
    // Float
    #[TestWith([1.0, ' ', true])]
    #[TestWith([1.0, '1', false])]
    #[TestWith([2.0, '1', true])]
    #[TestWith([1.0, 1, false])]
    #[TestWith([2.0, 1, true])]
    #[TestWith([1.0, 1.1, false])]
    #[TestWith([2.0, 1.1, true])]
    #[TestWith([2.0, null, true])]
    #[TestWith([2.0, true, false])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, true])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, false])]
    // Bool
    #[TestWith([true, true, false])]
    #[TestWith([true, false, true])]
    #[TestWith([true, '1', false])]
    #[TestWith([true, '0', true])]
    #[TestWith([true, 1, false])]
    #[TestWith([true, 0, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, false, false])]
    #[TestWith([false, '1', false])]
    #[TestWith([false, '0', false])]
    #[TestWith([false, 1, false])]
    #[TestWith([false, 0, false])]
    // Null
    #[TestWith([null, true, false])]
    #[TestWith([null, false, false])]
    #[TestWith([null, '1', false])]
    #[TestWith([null, '0', false])]
    #[TestWith([null, 1, false])]
    #[TestWith([null, 0, false])]
    public function testCompare_GreaterThan_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::GREATER_THAN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['2', []])]
    #[TestWith(['2', [1, 2, 3]])]
    #[TestWith(['2', new \stdClass()])]
    // Int
    #[TestWith([2, []])]
    #[TestWith([2, [1, 2, 3]])]
    #[TestWith([2, new \stdClass()])]
    // Float
    #[TestWith([2.0, []])]
    #[TestWith([2.0, [1, 2, 3]])]
    #[TestWith([2.0, new \stdClass()])]
    // Bool
    #[TestWith([true, []])]
    #[TestWith([true, [1, 2, 3]])]
    #[TestWith([true, new \stdClass()])]
    #[TestWith([false, []])]
    #[TestWith([false, [1, 2, 3]])]
    #[TestWith([false, new \stdClass()])]
    // Null
    #[TestWith([null, []])]
    #[TestWith([null, [1, 2, 3]])]
    #[TestWith([null, new \stdClass()])]
    // Array
    #[TestWith([[], null])]
    #[TestWith([[], 1])]
    #[TestWith([[1], 1])]
    #[TestWith([[2], 1])]
    #[TestWith([[], []])]
    #[TestWith([[], [1]])]
    #[TestWith([[1], []])]
    #[TestWith([[2], [1]])]
    #[TestWith([[1], [2]])]
    #[TestWith([['foo' => 1], ['bar' => 0]])]
    #[TestWith([[1], new \stdClass()])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass()])]
    public function testCompare_GreaterThan_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::GREATER_THAN->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
            strict: true,
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', false])]
    #[TestWith(['1', ' ', true])]
    #[TestWith(['1', '1', true])]
    #[TestWith(['2', '1', true])]
    #[TestWith(['1', 1, true])]
    #[TestWith(['2', 1, true])]
    #[TestWith(['1', 1.1, false])]
    #[TestWith(['2', 1.1, true])]
    #[TestWith(['2', null, true])]
    #[TestWith(['2', true, true])]
    // Int
    #[TestWith([1, ' ', true])]
    #[TestWith([1, '1', true])]
    #[TestWith([2, '1', true])]
    #[TestWith([1, 1, true])]
    #[TestWith([2, 1, true])]
    #[TestWith([1, 1.1, false])]
    #[TestWith([2, 1.1, true])]
    #[TestWith([2, null, true])]
    #[TestWith([2, true, true])]
        // Float
    #[TestWith([1.0, ' ', true])]
    #[TestWith([1.0, '1', true])]
    #[TestWith([2.0, '1', true])]
    #[TestWith([1.0, 1, true])]
    #[TestWith([2.0, 1, true])]
    #[TestWith([1.0, 1.1, false])]
    #[TestWith([2.0, 1.1, true])]
    #[TestWith([2.0, null, true])]
    #[TestWith([2.0, true, true])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, true])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, false, true])]
    #[TestWith([true, '1', true])]
    #[TestWith([true, '0', true])]
    #[TestWith([true, 1, true])]
    #[TestWith([true, 0, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, false, true])]
    #[TestWith([false, '1', false])]
    #[TestWith([false, '0', true])]
    #[TestWith([false, 1, false])]
    #[TestWith([false, 0, true])]
    // Null
    #[TestWith([null, true, false])]
    #[TestWith([null, false, true])]
    #[TestWith([null, '1', false])]
    #[TestWith([null, '0', false])]
    #[TestWith([null, 1, false])]
    #[TestWith([null, 0, true])]
    public function testCompare_GreaterThanOrEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::GREATER_THAN_OR_EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', false])]
    #[TestWith(['1', ' ', true])]
    #[TestWith(['1', '1', true])]
    #[TestWith(['2', '1', true])]
    #[TestWith(['1', 1, true])]
    #[TestWith(['2', 1, true])]
    #[TestWith(['1', 1.1, false])]
    #[TestWith(['2', 1.1, true])]
    #[TestWith(['2', null, true])]
    #[TestWith(['2', true, true])]
    // Int
    #[TestWith([1, ' ', true])]
    #[TestWith([1, '1', true])]
    #[TestWith([2, '1', true])]
    #[TestWith([1, 1, true])]
    #[TestWith([2, 1, true])]
    #[TestWith([1, 1.1, false])]
    #[TestWith([2, 1.1, true])]
    #[TestWith([2, null, true])]
    #[TestWith([2, true, true])]
    // Float
    #[TestWith([1.0, ' ', true])]
    #[TestWith([1.0, '1', true])]
    #[TestWith([2.0, '1', true])]
    #[TestWith([1.0, 1, true])]
    #[TestWith([2.0, 1, true])]
    #[TestWith([1.0, 1.1, false])]
    #[TestWith([2.0, 1.1, true])]
    #[TestWith([2.0, null, true])]
    #[TestWith([2.0, true, true])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, true])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, false, true])]
    #[TestWith([true, '1', true])]
    #[TestWith([true, '0', true])]
    #[TestWith([true, 1, true])]
    #[TestWith([true, 0, true])]
    #[TestWith([false, true, false])]
    #[TestWith([false, false, true])]
    #[TestWith([false, '1', false])]
    #[TestWith([false, '0', true])]
    #[TestWith([false, 1, false])]
    #[TestWith([false, 0, true])]
    // Null
    #[TestWith([null, true, false])]
    #[TestWith([null, false, true])]
    #[TestWith([null, '1', false])]
    #[TestWith([null, '0', false])]
    #[TestWith([null, 1, false])]
    #[TestWith([null, 0, true])]
    public function testCompare_GreaterThanOrEquals_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::GREATER_THAN_OR_EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['2', []])]
    #[TestWith(['2', [1, 2, 3]])]
    #[TestWith(['2', new \stdClass()])]
    // Int
    #[TestWith([2, []])]
    #[TestWith([2, [1, 2, 3]])]
    #[TestWith([2, new \stdClass()])]
    // Float
    #[TestWith([2.0, []])]
    #[TestWith([2.0, [1, 2, 3]])]
    #[TestWith([2.0, new \stdClass()])]
    // Bool
    #[TestWith([true, []])]
    #[TestWith([true, [1, 2, 3]])]
    #[TestWith([true, new \stdClass()])]
    #[TestWith([false, []])]
    #[TestWith([false, [1, 2, 3]])]
    #[TestWith([false, new \stdClass()])]
    // Null
    #[TestWith([null, []])]
    #[TestWith([null, [1, 2, 3]])]
    #[TestWith([null, new \stdClass()])]
    // Array
    #[TestWith([[], null])]
    #[TestWith([[], 1])]
    #[TestWith([[1], 1])]
    #[TestWith([[2], 1])]
    #[TestWith([[], []])]
    #[TestWith([[], [1]])]
    #[TestWith([[1], []])]
    #[TestWith([[2], [1]])]
    #[TestWith([[1], [2]])]
    #[TestWith([['foo' => 1], ['bar' => 0]])]
    #[TestWith([[1], new \stdClass()])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass()])]
    public function testCompare_GreaterThanOrEquals_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::GREATER_THAN_OR_EQUALS->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
            strict: true,
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', true])]
    #[TestWith(['1', ' ', false])]
    #[TestWith(['1', '1', false])]
    #[TestWith(['2', '1', false])]
    #[TestWith(['1', 1, false])]
    #[TestWith(['2', 1, false])]
    #[TestWith(['1', 1.1, true])]
    #[TestWith(['2', 1.1, false])]
    #[TestWith(['2', null, false])]
    #[TestWith(['2', true, false])]
    // Int
    #[TestWith([1, ' ', false])]
    #[TestWith([1, '1', false])]
    #[TestWith([2, '1', false])]
    #[TestWith([1, 1, false])]
    #[TestWith([2, 1, false])]
    #[TestWith([1, 1.1, true])]
    #[TestWith([2, 1.1, false])]
    #[TestWith([2, null, false])]
    #[TestWith([2, true, false])]
    // Float
    #[TestWith([1.0, ' ', false])]
    #[TestWith([1.0, '1', false])]
    #[TestWith([2.0, '1', false])]
    #[TestWith([1.0, 1, false])]
    #[TestWith([2.0, 1, false])]
    #[TestWith([1.0, 1.1, true])]
    #[TestWith([2.0, 1.1, false])]
    #[TestWith([2.0, null, false])]
    #[TestWith([2.0, true, false])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, false])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, false])]
    // Bool
    #[TestWith([true, true, false])]
    #[TestWith([true, false, false])]
    #[TestWith([true, '1', false])]
    #[TestWith([true, '0', false])]
    #[TestWith([true, 1, false])]
    #[TestWith([true, 0, false])]
    #[TestWith([false, true, true])]
    #[TestWith([false, false, false])]
    #[TestWith([false, '1', true])]
    #[TestWith([false, '0', false])]
    #[TestWith([false, 1, true])]
    #[TestWith([false, 0, false])]
    // Null
    #[TestWith([null, true, true])]
    #[TestWith([null, false, false])]
    #[TestWith([null, '1', true])]
    #[TestWith([null, '0', true])]
    #[TestWith([null, 1, true])]
    #[TestWith([null, 0, false])]
    public function testCompare_LessThan(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::LESS_THAN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', true])]
    #[TestWith(['1', ' ', false])]
    #[TestWith(['1', '1', false])]
    #[TestWith(['2', '1', false])]
    #[TestWith(['1', 1, false])]
    #[TestWith(['2', 1, false])]
    #[TestWith(['1', 1.1, true])]
    #[TestWith(['2', 1.1, false])]
    #[TestWith(['2', null, false])]
    #[TestWith(['2', true, false])]
    // Int
    #[TestWith([1, ' ', false])]
    #[TestWith([1, '1', false])]
    #[TestWith([2, '1', false])]
    #[TestWith([1, 1, false])]
    #[TestWith([2, 1, false])]
    #[TestWith([1, 1.1, true])]
    #[TestWith([2, 1.1, false])]
    #[TestWith([2, null, false])]
    #[TestWith([2, true, false])]
    // Float
    #[TestWith([1.0, ' ', false])]
    #[TestWith([1.0, '1', false])]
    #[TestWith([2.0, '1', false])]
    #[TestWith([1.0, 1, false])]
    #[TestWith([2.0, 1, false])]
    #[TestWith([1.0, 1.1, true])]
    #[TestWith([2.0, 1.1, false])]
    #[TestWith([2.0, null, false])]
    #[TestWith([2.0, true, false])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, false])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, false])]
    // Bool
    #[TestWith([true, true, false])]
    #[TestWith([true, false, false])]
    #[TestWith([true, '1', false])]
    #[TestWith([true, '0', false])]
    #[TestWith([true, 1, false])]
    #[TestWith([true, 0, false])]
    #[TestWith([false, true, true])]
    #[TestWith([false, false, false])]
    #[TestWith([false, '1', true])]
    #[TestWith([false, '0', false])]
    #[TestWith([false, 1, true])]
    #[TestWith([false, 0, false])]
    // Null
    #[TestWith([null, true, true])]
    #[TestWith([null, false, false])]
    #[TestWith([null, '1', true])]
    #[TestWith([null, '0', true])]
    #[TestWith([null, 1, true])]
    #[TestWith([null, 0, false])]
    public function testCompare_LessThan_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::LESS_THAN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['2', []])]
    #[TestWith(['2', [1, 2, 3]])]
    #[TestWith(['2', new \stdClass()])]
    // Int
    #[TestWith([2, []])]
    #[TestWith([2, [1, 2, 3]])]
    #[TestWith([2, new \stdClass()])]
    // Float
    #[TestWith([2.0, []])]
    #[TestWith([2.0, [1, 2, 3]])]
    #[TestWith([2.0, new \stdClass()])]
    // Bool
    #[TestWith([true, []])]
    #[TestWith([true, [1, 2, 3]])]
    #[TestWith([true, new \stdClass()])]
    #[TestWith([false, []])]
    #[TestWith([false, [1, 2, 3]])]
    #[TestWith([false, new \stdClass()])]
    // Null
    #[TestWith([null, []])]
    #[TestWith([null, [1, 2, 3]])]
    #[TestWith([null, new \stdClass()])]
    // Array
    #[TestWith([[], null])]
    #[TestWith([[], 1])]
    #[TestWith([[1], 1])]
    #[TestWith([[2], 1])]
    #[TestWith([[], []])]
    #[TestWith([[], [1]])]
    #[TestWith([[1], []])]
    #[TestWith([[2], [1]])]
    #[TestWith([[1], [2]])]
    #[TestWith([['foo' => 1], ['bar' => 0]])]
    #[TestWith([[1], new \stdClass()])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass()])]
    public function testCompare_LessThan_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::LESS_THAN->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
            strict: true,
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', true])]
    #[TestWith(['1', ' ', false])]
    #[TestWith(['1', '1', true])]
    #[TestWith(['2', '1', false])]
    #[TestWith(['1', 1, true])]
    #[TestWith(['2', 1, false])]
    #[TestWith(['1', 1.1, true])]
    #[TestWith(['2', 1.1, false])]
    #[TestWith(['2', null, false])]
    #[TestWith(['2', true, true])]
    // Int
    #[TestWith([1, ' ', false])]
    #[TestWith([1, '1', true])]
    #[TestWith([2, '1', false])]
    #[TestWith([1, 1, true])]
    #[TestWith([2, 1, false])]
    #[TestWith([1, 1.1, true])]
    #[TestWith([2, 1.1, false])]
    #[TestWith([2, null, false])]
    #[TestWith([2, true, true])]
    // Float
    #[TestWith([1.0, ' ', false])]
    #[TestWith([1.0, '1', true])]
    #[TestWith([2.0, '1', false])]
    #[TestWith([1.0, 1, true])]
    #[TestWith([2.0, 1, false])]
    #[TestWith([1.0, 1.1, true])]
    #[TestWith([2.0, 1.1, false])]
    #[TestWith([2.0, null, false])]
    #[TestWith([2.0, true, true])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, false])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, false, false])]
    #[TestWith([true, '1', true])]
    #[TestWith([true, '0', false])]
    #[TestWith([true, 1, true])]
    #[TestWith([true, 0, false])]
    #[TestWith([false, true, true])]
    #[TestWith([false, false, true])]
    #[TestWith([false, '1', true])]
    #[TestWith([false, '0', true])]
    #[TestWith([false, 1, true])]
    #[TestWith([false, 0, true])]
    // Null
    #[TestWith([null, true, true])]
    #[TestWith([null, false, true])]
    #[TestWith([null, '1', true])]
    #[TestWith([null, '0', true])]
    #[TestWith([null, 1, true])]
    #[TestWith([null, 0, true])]
    public function testCompare_LessThanOrEquals(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::LESS_THAN_OR_EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['', ' ', true])]
    #[TestWith(['1', ' ', false])]
    #[TestWith(['1', '1', true])]
    #[TestWith(['2', '1', false])]
    #[TestWith(['1', 1, true])]
    #[TestWith(['2', 1, false])]
    #[TestWith(['1', 1.1, true])]
    #[TestWith(['2', 1.1, false])]
    #[TestWith(['2', null, false])]
    #[TestWith(['2', true, true])]
    // Int
    #[TestWith([1, ' ', false])]
    #[TestWith([1, '1', true])]
    #[TestWith([2, '1', false])]
    #[TestWith([1, 1, true])]
    #[TestWith([2, 1, false])]
    #[TestWith([1, 1.1, true])]
    #[TestWith([2, 1.1, false])]
    #[TestWith([2, null, false])]
    #[TestWith([2, true, true])]
    // Float
    #[TestWith([1.0, ' ', false])]
    #[TestWith([1.0, '1', true])]
    #[TestWith([2.0, '1', false])]
    #[TestWith([1.0, 1, true])]
    #[TestWith([2.0, 1, false])]
    #[TestWith([1.0, 1.1, true])]
    #[TestWith([2.0, 1.1, false])]
    #[TestWith([2.0, null, false])]
    #[TestWith([2.0, true, true])]
    #[TestWith([3.14 + PHP_FLOAT_EPSILON, 3.14, false])]
    #[TestWith([3.14 + (PHP_FLOAT_EPSILON / 10), 3.14, true])]
    // Bool
    #[TestWith([true, true, true])]
    #[TestWith([true, false, false])]
    #[TestWith([true, '1', true])]
    #[TestWith([true, '0', false])]
    #[TestWith([true, 1, true])]
    #[TestWith([true, 0, false])]
    #[TestWith([false, true, true])]
    #[TestWith([false, false, true])]
    #[TestWith([false, '1', true])]
    #[TestWith([false, '0', true])]
    #[TestWith([false, 1, true])]
    #[TestWith([false, 0, true])]
    // Null
    #[TestWith([null, true, true])]
    #[TestWith([null, false, true])]
    #[TestWith([null, '1', true])]
    #[TestWith([null, '0', true])]
    #[TestWith([null, 1, true])]
    #[TestWith([null, 0, true])]
    public function testCompare_LessThanOrEquals_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::LESS_THAN_OR_EQUALS->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    // String
    #[TestWith(['2', []])]
    #[TestWith(['2', [1, 2, 3]])]
    #[TestWith(['2', new \stdClass()])]
    // Int
    #[TestWith([2, []])]
    #[TestWith([2, [1, 2, 3]])]
    #[TestWith([2, new \stdClass()])]
    // Float
    #[TestWith([2.0, []])]
    #[TestWith([2.0, [1, 2, 3]])]
    #[TestWith([2.0, new \stdClass()])]
    // Bool
    #[TestWith([true, []])]
    #[TestWith([true, [1, 2, 3]])]
    #[TestWith([true, new \stdClass()])]
    #[TestWith([false, []])]
    #[TestWith([false, [1, 2, 3]])]
    #[TestWith([false, new \stdClass()])]
    // Null
    #[TestWith([null, []])]
    #[TestWith([null, [1, 2, 3]])]
    #[TestWith([null, new \stdClass()])]
    // Array
    #[TestWith([[], null])]
    #[TestWith([[], 1])]
    #[TestWith([[1], 1])]
    #[TestWith([[2], 1])]
    #[TestWith([[], []])]
    #[TestWith([[], [1]])]
    #[TestWith([[1], []])]
    #[TestWith([[2], [1]])]
    #[TestWith([[1], [2]])]
    #[TestWith([['foo' => 1], ['bar' => 0]])]
    #[TestWith([[1], new \stdClass()])]
    // Object
    #[TestWith([new \stdClass(), new \stdClass()])]
    public function testCompare_LessThanOrEquals_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::LESS_THAN_OR_EQUALS->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
            strict: true,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_In(): array
    {
        return [
            ['1', [1, 2, 3], true],
            ['1', ['1', '2', '3'], true],
            ['1', ['1 ', '2', '3'], true],
            [1, [1, 2, 3], true],
            [1, [4, 5, 6], false],
            [1, ['1', '2', '3'], true],
            [1, [1 => 1, 2 => 2, 3 => 3], true],
            [1, ['1' => 1, '2' => 2, '3' => 3], true],
            [1, ['1' => '1', '2' => '2', '3' => '3'], true],
            [['a', 'b', 'c'], ['a', 'b', 'c'], false],
            [['a', 'b', 'c'], [['a', 'b', 'c']], true],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_In')]
    public function testCompare_In(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_In')]
    public function testCompare_In_Iterator(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = new TestIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_In')]
    public function testCompare_In_IteratorToArray(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = $this->getMockIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_In_Strict(): array
    {
        return [
            ['1', [1, 2, 3], false],
            ['1', ['1', '2', '3'], true],
            ['1', ['1 ', '2', '3'], false],
            [1, [1, 2, 3], true],
            [1, [4, 5, 6], false],
            [1, ['1', '2', '3'], false],
            [1, [1 => 1, 2 => 2, 3 => 3], true],
            [1, ['1' => 1, '2' => 2, '3' => 3], true],
            [1, ['1' => '1', '2' => '2', '3' => '3'], false],
            [['a', 'b', 'c'], ['a', 'b', 'c'], false],
            [['a', 'b', 'c'], [['a', 'b', 'c']], true],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_In_Strict')]
    public function testCompare_In_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_In_Strict')]
    public function testCompare_In_Strict_Iterator(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = new TestIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_In_Strict')]
    public function testCompare_In_Strict_IteratorToArray(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = $this->getMockIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    #[TestWith(['foo', 'bar'])]
    #[TestWith(['foo', null])]
    #[TestWith(['foo', true])]
    #[TestWith(['foo', 42])]
    #[TestWith(['foo', 3.14])]
    #[TestWith(['foo', new \stdClass()])]
    public function testCompare_In_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::IN->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_NotIn(): array
    {
        return [
            ['1', [1, 2, 3], false],
            ['1', ['1', '2', '3'], false],
            ['1', ['1 ', '2', '3'], false],
            [1, [1, 2, 3], false],
            [1, [4, 5, 6], true],
            [1, ['1', '2', '3'], false],
            [1, [1 => 1, 2 => 2, 3 => 3], false],
            [1, ['1' => 1, '2' => 2, '3' => 3], false],
            [1, ['1' => '1', '2' => '2', '3' => '3'], false],
            [['a', 'b', 'c'], ['a', 'b', 'c'], true],
            [['a', 'b', 'c'], [['a', 'b', 'c']], false],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn')]
    public function testCompare_NotIn(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn')]
    public function testCompare_NotIn_Iterator(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = new TestIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn')]
    public function testCompare_NotIn_IteratorToArray(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = $this->getMockIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_NotIn_Strict(): array
    {
        return [
            ['1', [1, 2, 3], true],
            ['1', ['1', '2', '3'], false],
            ['1', ['1 ', '2', '3'], true],
            [1, [1, 2, 3], false],
            [1, [4, 5, 6], true],
            [1, ['1', '2', '3'], true],
            [1, [1 => 1, 2 => 2, 3 => 3], false],
            [1, ['1' => 1, '2' => 2, '3' => 3], false],
            [1, ['1' => '1', '2' => '2', '3' => '3'], true],
            [['a', 'b', 'c'], ['a', 'b', 'c'], true],
            [['a', 'b', 'c'], [['a', 'b', 'c']], false],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn_Strict')]
    public function testCompare_NotIn_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn_Strict')]
    public function testCompare_NotIn_Strict_Iterator(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = new TestIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed $sourceValue
     * @param mixed[] $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotIn_Strict')]
    public function testCompare_NotIn_Strict_IteratorToArray(
        mixed $sourceValue,
        array $compareValue,
        bool $expectedResult,
    ): void {
        $compareValue = $this->getMockIterator($compareValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_IN->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    #[TestWith(['foo', 'bar'])]
    #[TestWith(['foo', null])]
    #[TestWith(['foo', true])]
    #[TestWith(['foo', 42])]
    #[TestWith(['foo', 3.14])]
    #[TestWith(['foo', new \stdClass()])]
    public function testCompare_NotIn_InvalidArgument(
        mixed $sourceValue,
        mixed $compareValue,
    ): void {
        $this->expectException(\InvalidArgumentException::class);

        Comparators::NOT_IN->compare(
            sourceValue: $sourceValue,
            compareValue: $compareValue,
        );
    }

    #[Test]
    #[TestWith([null, null, true])]
    #[TestWith([false, null, true])]
    #[TestWith(['', null, true])]
    #[TestWith([0, null, true])]
    #[TestWith([0.0, null, true])]
    #[TestWith([true, null, false])]
    #[TestWith([' ', null, false])]
    #[TestWith(['1', null, false])]
    #[TestWith([1, null, false])]
    #[TestWith([1.0, null, false])]
    #[TestWith([new \stdClass(), null, false])]
    #[TestWith([0, 'foo', true])]
    #[TestWith([1, 'foo', false])]
    #[TestWith([0, ['foo'], true])]
    #[TestWith([1, ['foo'], false])]
    public function testCompare_Empty(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_Empty_Array(): array
    {
        return [
            [[], null, true],
            [[null], null, false],
            [[1, 2, 3], null, false],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Array')]
    public function testCompare_Empty_Array(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Array')]
    public function testCompare_Empty_Array_Iterator(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = new TestIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Array')]
    public function testCompare_Empty_Array_Iterator_ToArray(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = $this->getMockIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    #[TestWith([null, null, true])]
    #[TestWith([false, null, true])]
    #[TestWith(['', null, true])]
    #[TestWith([0, null, true])]
    #[TestWith([0.0, null, true])]
    #[TestWith([true, null, false])]
    #[TestWith([' ', null, false])]
    #[TestWith(['1', null, false])]
    #[TestWith([1, null, false])]
    #[TestWith([1.0, null, false])]
    #[TestWith([new \stdClass(), null, false])]
    #[TestWith([0, 'foo', true])]
    #[TestWith([1, 'foo', false])]
    #[TestWith([0, ['foo'], true])]
    #[TestWith([1, ['foo'], false])]
    public function testCompare_Empty_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_Empty_Strict_Array(): array
    {
        return [
            [[], null, true],
            [[null], null, false],
            [[1, 2, 3], null, false],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Strict_Array')]
    public function testCompare_Empty_Strict_Array(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Strict_Array')]
    public function testCompare_Empty_Strict_Array_Iterator(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = new TestIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_Empty_Strict_Array')]
    public function testCompare_Empty_Strict_Array_Iterator_ToArray(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = $this->getMockIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    #[TestWith([null, null, false])]
    #[TestWith([false, null, false])]
    #[TestWith(['', null, false])]
    #[TestWith([0, null, false])]
    #[TestWith([0.0, null, false])]
    #[TestWith([true, null, true])]
    #[TestWith([' ', null, true])]
    #[TestWith(['1', null, true])]
    #[TestWith([1, null, true])]
    #[TestWith([1.0, null, true])]
    #[TestWith([new \stdClass(), null, true])]
    #[TestWith([0, 'foo', false])]
    #[TestWith([1, 'foo', true])]
    #[TestWith([0, ['foo'], false])]
    #[TestWith([1, ['foo'], true])]
    public function testCompare_NotEmpty(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_NotEmpty_Array(): array
    {
        return [
            [[], null, false],
            [[null], null, true],
            [[1, 2, 3], null, true],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Array')]
    public function testCompare_NotEmpty_Array(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Array')]
    public function testCompare_NotEmpty_Array_Iterator(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = new TestIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Array')]
    public function testCompare_NotEmpty_Array_Iterator_ToArray(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = $this->getMockIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    #[Test]
    #[TestWith([null, null, false])]
    #[TestWith([false, null, false])]
    #[TestWith(['', null, false])]
    #[TestWith([0, null, false])]
    #[TestWith([0.0, null, false])]
    #[TestWith([true, null, true])]
    #[TestWith([' ', null, true])]
    #[TestWith(['1', null, true])]
    #[TestWith([1, null, true])]
    #[TestWith([1.0, null, true])]
    #[TestWith([new \stdClass(), null, true])]
    #[TestWith([0, 'foo', false])]
    #[TestWith([1, 'foo', true])]
    #[TestWith([0, ['foo'], false])]
    #[TestWith([1, ['foo'], true])]
    public function testCompare_NotEmpty_Strict(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCompare_NotEmpty_Strict_Array(): array
    {
        return [
            [[], null, false],
            [[null], null, true],
            [[1, 2, 3], null, true],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Strict_Array')]
    public function testCompare_NotEmpty_Strict_Array(
        mixed $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Strict_Array')]
    public function testCompare_NotEmpty_Strict_Array_Iterator(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = new TestIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $sourceValue
     * @param mixed $compareValue
     * @param bool $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testCompare_NotEmpty_Strict_Array')]
    public function testCompare_NotEmpty_Strict_Array_Iterator_ToArray(
        array $sourceValue,
        mixed $compareValue,
        bool $expectedResult,
    ): void {
        $sourceValue = $this->getMockIterator($sourceValue);

        $this->assertSame(
            $expectedResult,
            Comparators::NOT_EMPTY->compare(
                sourceValue: $sourceValue,
                compareValue: $compareValue,
                strict: true,
            ),
            sprintf('Args: %s', json_encode(func_get_args())),
        );
    }

    /**
     * @param mixed[] $data
     * @return IteratorInterface
     */
    private function getMockIterator(array $data): IteratorInterface
    {
        $mockIterator = $this->getMockBuilder(IteratorInterface::class)
            ->disableOriginalConstructor()
            ->getMock();
        $mockIterator->method('toArray')->willReturn($data);

        return $mockIterator;
    }
}
