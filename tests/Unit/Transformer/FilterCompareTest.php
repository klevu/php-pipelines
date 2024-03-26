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

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\IteratorInterface;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\Pipeline\Context;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Test\Fixture\TestObject;
use Klevu\Pipelines\Transformer\FilterCompare;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterCompare::class)]
class FilterCompareTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                null,
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: null),
                        comparator: Comparators::EMPTY,
                        compareValue: null,
                    ),
                ]),
                null,
                null,
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: null),
                        comparator: Comparators::LESS_THAN,
                        compareValue: 2,
                    ),
                ]),
                new Context([]),
                [1],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: 2,
                        comparator: Comparators::GREATER_THAN,
                        compareValue: new Extraction(accessor: null),
                    ),
                ]),
                null,
                [1],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: null), 'lt', 2],
                ]),
                null,
                [1],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [2, 'gt', new Extraction(accessor: null)],
                ]),
                null,
                [1],
            ],
            [
                new TestIterator([1, 2, 3]), // no toArray()
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: null), 'lt', 2],
                ]),
                null,
                [1],
            ],
            [
                new TestIterator([1, 2, 3]), // no toArray()
                $argumentIteratorFactory->create([
                    [2, 'gt', new Extraction(accessor: null)],
                ]),
                null,
                [1],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: null), 'gte', 2],
                ]),
                null,
                [1 => 2, 3], // maintain key-value associations
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [2, 'lte', new Extraction(accessor: null)],
                ]),
                null,
                [1 => 2, 3], // maintain key-value associations
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: null), 'lte', new Extraction(accessor: null)],
                ]),
                null,
                [1, 2, 3],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: 'config::compareValue'), 'gte', new Extraction(accessor: null)],
                ]),
                new Context([
                    'config' => [
                        'compareValue' => 2,
                    ],
                ]),
                [1, 2],
            ],
            [
                [1, 2, 3],
                $argumentIteratorFactory->create([
                    [new Extraction(accessor: null), 'lte', new Extraction(accessor: 'config::compareValue')],
                ]),
                new Context([
                    'config' => [
                        'compareValue' => 2,
                    ],
                ]),
                [1, 2],
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success')]
    public function testTransform_Success(
        mixed $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $filterCompare = new FilterCompare();

        $result = $filterCompare->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
    }

    #[Test]
    public function testTransform_Success_MockIterator(): void
    {
        $filterCompare = new FilterCompare();

        $result = $filterCompare->transform(
            data: $this->getMockIterator([1, 2, 3]),
            arguments: new ArgumentIterator([
                new Argument(
                    new ArgumentIterator([
                        new Argument(
                            new Extraction(null),
                        ),
                        new Argument('lt'),
                        new Argument(2),
                    ]),
                ),
            ]),
        );
        $this->assertSame([1], $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success_MultipleConditions(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [1, 2, 3, 4, 5, 6, 7, 8, 9, 10],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: null),
                        comparator: Comparators::GREATER_THAN_OR_EQUALS,
                        compareValue: 8,
                    ),
                    [new Extraction(accessor: ''), 'in', [2, 4, 6]],
                ]),
                null,
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
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: null),
                        comparator: Comparators::NOT_EMPTY,
                        compareValue: null,
                    ),
                    [new Extraction(accessor: ''), 'in', [0]],
                ]),
                null,
                [0, false, null, 'foo'],
            ],
            [
                [0, false, null, 'foo'],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: null),
                        comparator: Comparators::NOT_EMPTY,
                        compareValue: null,
                    ),
                    [new Extraction(accessor: ''), 'in', [0], true],
                ]),
                null,
                [
                    0 => 0,
                    3 => 'foo',
                ],
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success_MultipleConditions')]
    public function testTransform_Success_MultipleConditions(
        mixed $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $filterCompare = new FilterCompare();

        $result = $filterCompare->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success_WithSourceAccessor(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new TestObject(publicProperty: 1),
                    new TestObject(publicProperty: 2),
                    new TestObject(publicProperty: 3),
                ],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'publicProperty'),
                        comparator: Comparators::LESS_THAN,
                        compareValue: 2,
                    ),
                ]),
                null,
                [
                    new TestObject(publicProperty: 1),
                ],
            ],
            [
                [
                    'foo' => new TestObject(privateProperty: [
                        'foo' => 1,
                    ]),
                    'bar' => new TestObject(privateProperty: [
                        'foo' => 2,
                    ]),
                    'baz' => new TestObject(privateProperty: [
                        'foo' => 3,
                    ]),
                ],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getPrivateProperty().foo'),
                        comparator: Comparators::GREATER_THAN,
                        compareValue: 2,
                    ),
                ]),
                null,
                [
                    'baz' => new TestObject(privateProperty: [
                        'foo' => 3,
                    ]),
                ],
            ],
            [
                [
                    'foo' => new TestObject(privateProperty: 1),
                    'bar' => new TestObject(privateProperty: 2),
                    'baz' => new TestObject(privateProperty: 3),
                ],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'foo::bar'), // From context
                        comparator: Comparators::EQUALS,
                        compareValue: 2,
                    ),
                ]),
                new Context([
                    'foo' => [
                        'bar' => 2,
                    ],
                ]),
                [
                    'foo' => new TestObject(privateProperty: 1),
                    'bar' => new TestObject(privateProperty: 2),
                    'baz' => new TestObject(privateProperty: 3),
                ],
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success_WithSourceAccessor')]
    public function testTransform_Success_WithSourceAccessor(
        mixed $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $filterCompare = new FilterCompare();

        $result = $filterCompare->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertEquals($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                'foo',
                $argumentIteratorFactory->create([
                    ['', 'nempty'],
                ]),
                null,
            ],
            [
                new \stdClass(),
                $argumentIteratorFactory->create([
                    ['', 'nempty'],
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidData')]
    public function testTransform_InvalidData(
        mixed $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $filterCompare = new FilterCompare();

        $this->expectException(InvalidInputDataException::class);
        $filterCompare->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                ['foo'],
                null,
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    null,
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    true,
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    'foo',
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    42,
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    3.14,
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    [],
                ]),
                null,
            ],
            [
                ['foo'],
                $argumentIteratorFactory->create([
                    new \stdClass(),
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param mixed $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidArguments')]
    public function testTransform_InvalidArguments(
        mixed $data,
        mixed $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $filterCompare = new FilterCompare();

        $this->expectException(InvalidTransformationArgumentsException::class);
        $filterCompare->transform(
            data: $data,
            arguments: $arguments, // @phpstan-ignore-line
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_AccessorException(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    'foo' => new TestObject(
                        privateProperty: new TestObject(publicProperty: true),
                    ),
                    'bar' => new TestObject(
                        privateProperty: null,
                    ),
                    'baz' => new TestObject(
                        privateProperty: new TestObject(publicProperty: null),
                    ),
                ],
                $argumentIteratorFactory->create([
                    new FilterComparison(
                        sourceValue: new Extraction(accessor: 'getPrivateProperty().publicProperty'),
                        comparator: Comparators::NOT_EMPTY,
                        compareValue: null,
                    ),
                ]),
                null,
                [
                    'foo' => new TestObject(
                        privateProperty: new TestObject(publicProperty: true),
                    ),
                    // bar and baz are filtered because failed extraction returns null, which is empty
                ],
            ],
        ];
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
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context,
        mixed $expectedResult,
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
