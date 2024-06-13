<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\Pipeline\Context;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Test\Fixture\TestObject;
use Klevu\Pipelines\Transformer\FilterCompare;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(FilterCompare::class)]
class FilterCompareTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = FilterCompare::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Array(),
            self::dataProvider_testTransform_Valid_Iterator(),
            self::dataProvider_testTransform_Valid_AllComparators(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array(): array
    {
        $testObjectPub1 = new TestObject(publicProperty: 1);
        $testObjectPub2 = new TestObject(publicProperty: 2);
        $testObjectPub3 = new TestObject(publicProperty: 3);
        $testObjectPriv1 = new TestObject(privateProperty: 1);
        $testObjectPriv2 = new TestObject(privateProperty: 2);
        $testObjectPriv3 = new TestObject(privateProperty: 3);

        $testObjectPrivFoo1 = new TestObject(privateProperty: [
            'foo' => 1,
        ]);
        $testObjectPrivFoo2 = new TestObject(privateProperty: [
            'foo' => 2,
        ]);
        $testObjectPrivFoo3 = new TestObject(privateProperty: [
            'foo' => 3,
        ]);

        return self::convertFixtures(
            fixtures: [
                [
                    [1, 2, 3],
                    [
                        [new Extraction(null), 'lt', 2],
                    ],
                    [1],
                ],
                [
                    [1, 2, 3],
                    [
                        [2, 'gt', new Extraction(null)],
                    ],
                    [1],
                ],
                [
                    [1, 2, 3],
                    [
                        [new Extraction(null), 'gte', 2],
                    ],
                    [ // Retain key-value associations
                        1 => 2,
                        2 => 3,
                    ],
                ],
                [
                    [1, 2, 3],
                    [
                        // OR conditions
                        [new Extraction(null), 'lte', 2],
                        [new Extraction(null), 'gte', 2],
                    ],
                    [1, 2, 3],
                ],
                [
                    ['a', 'b', 'c'],
                    [
                        [new Extraction(null), 'eq', 'a'],
                    ],
                    ['a'],
                ],
                [
                    [
                        ['a'],
                        [],
                    ],
                    [
                        [new Extraction(null), 'empty', null],
                    ],
                    [
                        1 => [],
                    ],
                ],
                [
                    [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                    [
                        [new Extraction(accessor: null), 'gte', 8],
                        [new Extraction(accessor: ''), 'in', [2, 4, 6]],
                    ],
                    [
                        1 => 2,
                        3 => 4,
                        5 => 6,
                        7 => 8,
                        8 => 9,
                        9 => 10,
                    ],
                ],
                [
                    [0, false, null, 'foo'],
                    [
                        [new Extraction(accessor: null), 'nempty', null],
                        [new Extraction(accessor: ''), 'in', [0]],
                    ],
                    [0, false, null, 'foo'],
                ],
                [
                    [0, false, null, 'foo'],
                    [
                        [new Extraction(accessor: null), 'nempty', null],
                        [new Extraction(accessor: ''), 'in', [0], true],
                    ],
                    [
                        0 => 0,
                        3 => 'foo',
                    ],
                ],
                [
                    [
                        $testObjectPub1,
                        $testObjectPub2,
                        $testObjectPub3,
                    ],
                    [
                        [new Extraction(accessor: 'publicProperty'), 'lt', 2],
                    ],
                    [
                        $testObjectPub1,
                    ],
                ],
                [
                    [
                        'foo' => $testObjectPrivFoo1,
                        'bar' => $testObjectPrivFoo2,
                        'baz' => $testObjectPrivFoo3,
                    ],
                    [
                        [new Extraction(accessor: 'getPrivateProperty().foo'), 'gt', 2],
                    ],
                    [
                        'baz' => $testObjectPrivFoo3,
                    ],
                ],
                [
                    [
                        'foo' => $testObjectPriv1,
                        'bar' => $testObjectPriv2,
                        'baz' => $testObjectPriv3,
                    ],
                    [
                        [new Extraction(accessor: 'foo::bar'), 'eq', 2],
                    ],
                    [
                        'foo' => $testObjectPriv1,
                        'bar' => $testObjectPriv2,
                        'baz' => $testObjectPriv3,
                    ],
                    [
                        'foo' => [
                            'bar' => 2,
                        ],
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Iterator(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    new TestIterator([1, 2, 3]),
                    [
                        [new Extraction(null), 'lt', 2],
                    ],
                    [1],
                ],
                [
                    new TestIterator([1, 2, 3]),
                    [
                        [2, 'gt', new Extraction(null)],
                    ],
                    [1],
                ],
                [
                    new TestIterator([1, 2, 3]),
                    [
                        [new Extraction(null), 'gte', 2],
                    ],
                    [ // Maintain key-value associations
                        1 => 2,
                        2 => 3,
                    ],
                ],
                [
                    new TestIterator([1, 2, 3]),
                    [
                        // OR conditions
                        [new Extraction(null), 'lte', 2],
                        [new Extraction(null), 'gte', 2],
                    ],
                    [1, 2, 3],
                ],
                [
                    new TestIterator([
                        ['a'],
                        [],
                    ]),
                    [
                        [new Extraction(null), 'empty', null],
                    ],
                    [
                        1 => [],
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_AllComparators(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Equals(),
            self::dataProvider_testTransform_Valid_NotEquals(),
            self::dataProvider_testTransform_Valid_GreaterThan(),
            self::dataProvider_testTransform_Valid_GreaterThanOrEquals(),
            self::dataProvider_testTransform_Valid_LessThan(),
            self::dataProvider_testTransform_Valid_LessThanOrEquals(),
            self::dataProvider_testTransform_Valid_In(),
            self::dataProvider_testTransform_Valid_NotIn(),
            self::dataProvider_testTransform_Valid_Empty(),
            self::dataProvider_testTransform_Valid_NotEmpty(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Equals(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['a', 'b', 'c'],
                    [
                        [new Extraction(null), 'eq', 'b'],
                    ],
                    [1 => 'b'],
                ],
                [
                    [3, 2, 1],
                    [
                        [new Extraction(null), 'eq', '1'],
                    ],
                    [2 => 1],
                ],
                [
                    [3, 2, 1],
                    [
                        [new Extraction(null), 'eq', '1', true],
                    ],
                    [],
                ],
                [
                    [[3], [2], [1]],
                    [
                        [new Extraction(null), 'eq', [1]],
                    ],
                    [2 => [1]],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'eq', 1],
                    ],
                    [true, 1, '1'],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'eq', 1, true],
                    ],
                    [1 => 1],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'eq', new Extraction(null)],
                    ],
                    [true, 1, '1', false, 0, '0'],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'eq', new Extraction(null), true],
                    ],
                    [true, 1, '1', false, 0, '0'],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction('compareValue::'), 'eq', new Extraction(null)],
                    ],
                    [0 => true, 1 => 1, 2 => '1', 6 => 1.0],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction('compareValue::'), 'eq', new Extraction(null), true],
                    ],
                    [6 => 1.0],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction(null), 'eq', new Extraction('compareValue::')],
                    ],
                    [0 => true, 1 => 1, 2 => '1', 6 => 1.0],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction(null), 'eq', new Extraction('compareValue::'), true],
                    ],
                    [6 => 1.0],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_NotEquals(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['a', 'b', 'c'],
                    [
                        [new Extraction(null), 'neq', 'b'],
                    ],
                    [0 => 'a', 2 => 'c'],
                ],
                [
                    [3, 2, 1],
                    [
                        [new Extraction(null), 'neq', '1'],
                    ],
                    [3, 2],
                ],
                [
                    [3, 2, 1],
                    [
                        [new Extraction(null), 'neq', '1', true],
                    ],
                    [3, 2, 1],
                ],
                [
                    [[3], [2], [1]],
                    [
                        [new Extraction(null), 'neq', [1]],
                    ],
                    [[3], [2]],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'neq', 1],
                    ],
                    [3 => false, 0, '0'],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'neq', 1, true],
                    ],
                    [true, 2 => '1', false, 0, '0'],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'neq', new Extraction(null)],
                    ],
                    [],
                ],
                [
                    [true, 1, '1', false, 0, '0'],
                    [
                        [new Extraction(null), 'neq', new Extraction(null), true],
                    ],
                    [],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction('compareValue::'), 'neq', new Extraction(null)],
                    ],
                    [3 => false, 4 => 0, 5 => '0'],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction('compareValue::'), 'neq', new Extraction(null), true],
                    ],
                    [true, 1, '1', false, 0, '0'],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction(null), 'neq', new Extraction('compareValue::')],
                    ],
                    [3 => false, 4 => 0, 5 => '0'],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
                [
                    [true, 1, '1', false, 0, '0', 1.0],
                    [
                        [new Extraction(null), 'neq', new Extraction('compareValue::'), true],
                    ],
                    [true, 1, '1', false, 0, '0'],
                    [
                        'compareValue' => 1.0,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_GreaterThan(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gt', 0.1],
                    ],
                    [1 => 0.2],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gt', new Extraction(null)],
                    ],
                    [],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gt', new Extraction('compareValue::')],
                    ],
                    [1 => 0.2],
                    [
                        'compareValue' => 0.1,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_GreaterThanOrEquals(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gte', 0.1],
                    ],
                    [0.1, 0.2],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gte', new Extraction(null)],
                    ],
                    [0.1, 0.2, -0.1],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'gte', new Extraction('compareValue::')],
                    ],
                    [0.1, 0.2],
                    [
                        'compareValue' => 0.1,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_LessThan(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lt', 0.1],
                    ],
                    [2 => -0.1],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lt', new Extraction(null)],
                    ],
                    [],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lt', new Extraction('compareValue::')],
                    ],
                    [2 => -0.1],
                    [
                        'compareValue' => 0.1,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_LessThanOrEquals(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lte', 0.1],
                    ],
                    [0 => 0.1, 2 => -0.1],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lte', new Extraction(null)],
                    ],
                    [0.1, 0.2, -0.1],
                ],
                [
                    [0.1, 0.2, -0.1],
                    [
                        [new Extraction(null), 'lte', new Extraction('compareValue::')],
                    ],
                    [0 => 0.1, 2 => -0.1],
                    [
                        'compareValue' => 0.1,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_In(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'in', [1]],
                    ],
                    [0 => 1, 2 => '1', 3 => 1],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'in', [1], true],
                    ],
                    [0 => 1, 3 => 1],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'in', new Extraction('compareValue::'), true],
                    ],
                    [0 => 1, 3 => 1],
                    [
                        'compareValue' => [1],
                    ],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'in', new Extraction('compareValue::'), true],
                    ],
                    [0 => 1, 3 => 1],
                    [
                        'compareValue' => [1],
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_NotIn(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'nin', [1]],
                    ],
                    [1 => 2, 4 => 'foo'],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'nin', [1], true],
                    ],
                    [1 => 2, 2 => '1', 4 => 'foo'],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'nin', new Extraction('compareValue::')],
                    ],
                    [1 => 2, 4 => 'foo'],
                    [
                        'compareValue' => [1],
                    ],
                ],
                [
                    [1, 2, '1', 1, 'foo'],
                    [
                        [new Extraction(null), 'nin', new Extraction('compareValue::'), true],
                    ],
                    [1 => 2, 2 => '1', 4 => 'foo'],
                    [
                        'compareValue' => [1],
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Empty(): array
    {
        $emptyObject = (object)[];
        $emptyArgumentIterator = new ArgumentIterator([]);

        return self::convertFixtures(
            fixtures: [
                [
                    [[], [null], 0, false, ' ', $emptyObject, $emptyArgumentIterator],
                    [
                        [new Extraction(null), 'empty', 'this does not matter'],
                    ],
                    [0 => [], 2 => 0, 3 => false, 6 => $emptyArgumentIterator],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_NotEmpty(): array
    {
        $emptyObject = (object)[];
        $emptyArgumentIterator = new ArgumentIterator([]);

        return self::convertFixtures(
            fixtures: [
                [
                    [[], [null], 0, false, ' ', $emptyObject, $emptyArgumentIterator],
                    [
                        [new Extraction(null), 'nempty', 'this does not matter'],
                    ],
                    [1 => [null], 4 => ' ', 5 => $emptyObject],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidInputData(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return [
            ['foo'],
            [42],
            [3.14],
            [true],
            [(object)['foo']],
            [$fileHandle],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['foo'],
                    [
                        null,
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        true,
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        'foo',
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        42,
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        3.14,
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        [],
                    ],
                    null,
                ],
                [
                    ['foo'],
                    [
                        new \stdClass(),
                    ],
                    null,
                ],
            ],
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_AccessorException(): array
    {
        $testObjectFoo = new TestObject(
            privateProperty: new TestObject(publicProperty: true),
        );
        $testObjectBar = new TestObject(
            privateProperty: null,
        );
        $testObjectBaz = new TestObject(
            privateProperty: new TestObject(publicProperty: null),
        );

        return self::convertFixtures(
            fixtures: [
                [
                    [
                        'foo' => $testObjectFoo,
                        'bar' => $testObjectBar,
                        'baz' => $testObjectBaz,
                    ],
                    [
                        [new Extraction(accessor: 'getPrivateProperty().publicProperty'), 'nempty', null],
                    ],
                    [
                        'foo' => $testObjectFoo,
                        // bar and baz are filtered because failed extraction returns null, which is empty
                    ],
                ],
            ],
        );
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     * @todo Support nullsafe extractions KS-18704
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_AccessorException')]
    public function testTransform_AccessorException(
        mixed $data,
        mixed $expectedResult,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        $filterCompare = new FilterCompare();

        $result = $filterCompare->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertEquals(
            expected: $expectedResult,
            actual: $result,
        );
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $return = array_merge(
            ...array_map(
                callback: static fn (array $data): array => [
                    [
                        $data[0],
                        $data[2],
                        $argumentIteratorFactory->create(
                            arguments: array_map(
                                callback: static fn (mixed $filterComparisonData): mixed => (is_array($filterComparisonData) && count($filterComparisonData) >= 3) // phpcs:ignore Generic.Files.LineLength.TooLong
                                    ? new FilterComparison(
                                        sourceValue: $filterComparisonData[0],
                                        comparator: Comparators::from($filterComparisonData[1]),
                                        compareValue: $filterComparisonData[2],
                                        strict: $filterComparisonData[3] ?? false,
                                    )
                                    : $filterComparisonData,
                                // @phpstan-ignore-next-line We know this is an array
                                array: $data[1] ?: [],
                            ),
                        ),
                        !empty($data[3])
                            ? new Context($data[3])
                            : null,
                    ],
                ],
                array: $fixtures,
            ),
        );

        return $return;
    }
}
