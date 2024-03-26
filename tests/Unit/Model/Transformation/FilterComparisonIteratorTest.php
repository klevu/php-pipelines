<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Model\Transformation;

use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\Model\Transformation\FilterComparisonIterator;
use Klevu\Pipelines\Test\Unit\Model\AbstractIteratorTestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(FilterComparisonIterator::class)]
class FilterComparisonIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @var string
     */
    protected string $iteratorFqcn = FilterComparisonIterator::class;
    /**
     * @var string
     */
    protected string $itemFqcn = FilterComparison::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_valid(): array
    {
        return [
            [
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getFoo()'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getBar()'),
                        comparator: Comparators::IN,
                        compareValue: [1, 2],
                        strict: false,
                    ),
                    new FilterComparison(
                        sourceValue: "foo",
                        comparator: Comparators::IN,
                        compareValue: [1, 2],
                        strict: false,
                    ),
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_invalid(): array
    {
        return [
            [
                [
                    (object)['foo' => 'bar'],
                ],
            ],
            [
                [
                    'foo',
                ],
            ],
            [
                [
                    12345,
                ],
            ],
            [
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getFoo()'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                    null,
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_filter(): array
    {
        return [
            [
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getFoo()'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                    new FilterComparison(
                        sourceValue: "foo",
                        comparator: Comparators::IN,
                        compareValue: [1, 2],
                        strict: false,
                    ),
                ],
                static fn (FilterComparison $filterComparison): bool => $filterComparison->strict,
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getFoo()'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                ],
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_walk(): array
    {
        return [
            [
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getFoo()'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                    new FilterComparison(
                        sourceValue: "foo",
                        comparator: Comparators::IN,
                        compareValue: [1, 2],
                        strict: false,
                    ),
                ],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                static function (FilterComparison &$filterComparison): void {
                    $filterComparison = new FilterComparison(
                        sourceValue: new Extraction(accessor: 'foo'),
                        comparator: $filterComparison->comparator,
                        compareValue: $filterComparison->compareValue,
                        strict: true,
                    );
                },
                [
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'foo'),
                        comparator: Comparators::EQUALS,
                        compareValue: 'bar',
                        strict: true,
                    ),
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'foo'),
                        comparator: Comparators::IN,
                        compareValue: [1, 2],
                        strict: true,
                    ),
                ],
            ],
        ];
    }
}
