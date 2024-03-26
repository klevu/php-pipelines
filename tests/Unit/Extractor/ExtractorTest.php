<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Extractor;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Pipeline\Context;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Test\Fixture\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extractor::class)]
class ExtractorTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_Object_Success(): array
    {
        return [
            // Empty accessor
            [
                new TestObject(privateProperty: 'foo'),
                ' ', // empty after trim
                null,
                new TestObject(privateProperty: 'foo'),
            ],
            [
                new TestObject(privateProperty: 'foo'),
                'getPrivateProperty().', // trailing separator
                null,
                'foo',
            ],
            // Method accessors
            [
                new TestObject(privateProperty: 'foo'),
                'getPrivateProperty()',
                null,
                'foo',
            ],
            [
                new TestObject(publicProperty: 'foo'),
                'getPrivateProperty()',
                null,
                null,
            ],
            // Property Accessors
            [
                new TestObject(privateProperty: 'foo'),
                'publicProperty',
                null,
                null,
            ],
            [
                new TestObject(publicProperty: 'foo'),
                'publicProperty',
                null,
                'foo',
            ],
            // Chained accessors
            [
                new TestObject(
                    publicProperty: new TestObject(publicProperty: 'bar'),
                ),
                'publicProperty.publicProperty', // property -> property
                null,
                'bar',
            ],
            [
                new TestObject(
                    privateProperty: new TestObject(publicProperty: 'bar'),
                ),
                'getPrivateProperty().publicProperty', // method -> property
                null,
                'bar',
            ],
            [
                new TestObject(
                    publicProperty: new TestObject(privateProperty: 'bar'),
                ),
                'publicProperty.getPrivateProperty()', // property -> method
                null,
                'bar',
            ],
            [
                new TestObject(
                    privateProperty: new TestObject(privateProperty: 'bar'),
                ),
                'getPrivateProperty().getPrivateProperty()', // method -> method
                null,
                'bar',
            ],
            // Context
            [
                new TestObject(),
                'config::property', // empty after trim
                new Context([
                    'config' => [
                        'property' => 'foo',
                    ],
                ]),
                'foo',
            ],
            [
                new TestObject(),
                'config::property.publicProperty', // empty after trim
                new Context([
                    'config' => [
                        'property' => new TestObject(publicProperty: 'foo'),
                    ],
                ]),
                'foo',
            ],
            [
                new TestObject(),
                'config::property.getPrivateProperty()', // empty after trim
                new Context([
                    'config' => [
                        'property' => new TestObject(privateProperty: 'foo'),
                    ],
                ]),
                'foo',
            ],
            [
                new TestObject(),
                'config::publicProperty', // empty after trim
                new Context([
                    'config' => new TestObject(publicProperty: 'foo'),
                ]),
                'foo',
            ],
            [
                new TestObject(),
                'config::getPrivateProperty()', // empty after trim
                new Context([
                    'config' => new TestObject(privateProperty: 'foo'),
                ]),
                'foo',
            ],
            [
                new TestObject(
                    publicProperty: new TestObject(privateProperty: 'bar'),
                ),
                'publicProperty.', // trailing separator
                null,
                new TestObject(privateProperty: 'bar'),
            ],
            [
                new TestObject(
                    privateProperty: new TestObject(privateProperty: 'bar'),
                ),
                'getPrivateProperty()..getPrivateProperty()', // double separator
                null,
                'bar',
            ],
            [
                new TestObject(
                    privateProperty: new TestObject(privateProperty: 'bar'),
                ),
                '.getPrivateProperty()', // leading separator
                new Context(),
                new TestObject(privateProperty: 'bar'),
            ],
            [
                new TestObject(
                    publicProperty: new TestObject(privateProperty: 'bar'),
                ),
                '.publicProperty', // leading separator
                new Context([]),
                new TestObject(privateProperty: 'bar'),
            ],
        ];
    }

    /**
     * @param object $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_Object_Success')]
    public function testExtract_Object_Success(
        object $source,
        string $accessor,
        ?\ArrayAccess $context,
        mixed $expectedResult,
    ): void {
        $extractor = new Extractor();

        $result = $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );

        if (is_object($expectedResult)) {
            $this->assertEquals($expectedResult, $result);
        } else {
            $this->assertSame($expectedResult, $result);
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_Object_Error(): array
    {
        return [
            [
                new TestObject(privateProperty: 'foo'),
                'privateProperty',
                null,
            ],
            [
                new TestObject(privateProperty: 'foo'),
                'nonExistentProperty',
                null,
            ],
            [
                new TestObject(publicProperty: 'foo'),
                'getPublicProperty()',
                null,
            ],
        ];
    }

    /**
     * @param mixed $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_Object_Error')]
    public function testExtract_Object_Error(
        mixed $source,
        string $accessor,
        ?\ArrayAccess $context,
    ): void {
        $extractor = new Extractor();

        $this->expectException(ExtractionException::class);
        $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_Array_Success(): array
    {
        return [
            // Empty accessor
            [
                [
                    'property' => 'foo',
                ],
                ' ', // empty after trim
                null,
                [
                    'property' => 'foo',
                ],
            ],
            [
                [
                    'property' => 'foo',
                ],
                'property.', // trailing separator
                null,
                'foo',
            ],
            // Chained accessors
            [
                [
                    'property' => new TestObject(publicProperty: 'bar'),
                ],
                'property.publicProperty', // property -> property
                null,
                'bar',
            ],
            [
                [
                    'property' => new TestObject(privateProperty: 'bar'),
                ],
                'property.getPrivateProperty()', // property -> method
                null,
                'bar',
            ],
            // Context
            [
                [],
                'config::property', // empty after trim
                new Context([
                    'config' => [
                        'property' => 'foo',
                    ],
                ]),
                'foo',
            ],
            [
                [
                    'property' => [
                        'subproperty' => 'foo',
                    ],
                ],
                'property.', // trailing separator
                null,
                [
                    'subproperty' => 'foo',
                ],
            ],
            [
                [
                    'property' => [
                        'subproperty' => 'foo',
                    ],
                ],
                'property..subproperty', // double separator
                null,
                'foo',
            ],
            [
                [
                    'property' => [
                        'subproperty' => 'foo',
                    ],
                ],
                '.property', // leading separator
                null,
                [
                    'subproperty' => 'foo',
                ],
            ],
        ];
    }

    /**
     * @param object|mixed[] $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_Array_Success')]
    public function testExtract_Array_Success(
        object|array $source,
        string $accessor,
        ?\ArrayAccess $context,
        mixed $expectedResult,
    ): void {
        $extractor = new Extractor();

        $result = $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_Array_Error(): array
    {
        return [
            [
                [
                    'property' => 'foo',
                ],
                'nonExistentProperty',
                null,
            ],
            [
                [
                    'property' => 'foo',
                ],
                'property()',
                null,
            ],
        ];
    }

    /**
     * @param mixed $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_Array_Error')]
    public function testExtract_Array_Error(
        mixed $source,
        string $accessor,
        ?\ArrayAccess $context,
    ): void {
        $extractor = new Extractor();

        $this->expectException(ExtractionException::class);
        $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_InvalidType(): array
    {
        return [
            [
                null,
                'foo',
                null,
            ],
            [
                null,
                'getFoo()',
                null,
            ],
            [
                true,
                'foo',
                null,
            ],
            [
                true,
                'getFoo()',
                null,
            ],
            [
                'foo',
                'foo',
                null,
            ],
            [
                'foo',
                'getFoo()',
                null,
            ],
            [
                42,
                'foo',
                null,
            ],
            [
                42,
                'getFoo()',
                null,
            ],
            [
                3.14,
                'foo',
                null,
            ],
            [
                3.14,
                'getFoo()',
                null,
            ],
        ];
    }

    /**
     * @param mixed $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_InvalidType')]
    public function testExtract_InvalidType(
        mixed $source,
        string $accessor,
        ?\ArrayAccess $context,
    ): void {
        $extractor = new Extractor();

        $this->expectException(ExtractionException::class);
        $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExtract_IterableMethod_Success(): array
    {
        return [
            [
                [
                    new TestObject(privateProperty: 'foo'),
                    new TestObject(privateProperty: ['wom' => 'bat']),
                    new TestObject(privateProperty: null),
                    new TestObject(privateProperty: (object)[1, 2, 3]),
                ],
                'getPrivateProperty()',
                null,
                [
                    'foo',
                    ['wom' => 'bat'],
                    null,
                    (object)[1, 2, 3],
                ],
            ],
            [
                new TestIterator([
                    new TestObject(privateProperty: 'foo'),
                    new TestObject(privateProperty: ['wom' => 'bat']),
                    new TestObject(privateProperty: null),
                    new TestObject(privateProperty: (object)[1, 2, 3]),
                ]),
                'getPrivateProperty()',
                null,
                [
                    'foo',
                    ['wom' => 'bat'],
                    null,
                    (object)[1, 2, 3],
                ],
            ],
            [
                new TestObject(privateProperty: false),
                'array::getPrivateProperty()',
                new Context([
                    'array' => [
                        new TestObject(privateProperty: 'foo'),
                        new TestObject(privateProperty: ['wom' => 'bat']),
                        new TestObject(privateProperty: null),
                        new TestObject(privateProperty: (object)[1, 2, 3]),
                    ],
                ]),
                [
                    'foo',
                    ['wom' => 'bat'],
                    null,
                    (object)[1, 2, 3],
                ],
            ],
            [
                new TestObject(privateProperty: false),
                'iterable::getPrivateProperty()',
                new Context([
                    'iterable' => new TestIterator([
                        new TestObject(privateProperty: 'foo'),
                        new TestObject(privateProperty: ['wom' => 'bat']),
                        new TestObject(privateProperty: null),
                        new TestObject(privateProperty: (object)[1, 2, 3]),
                    ]),
                ]),
                [
                    'foo',
                    ['wom' => 'bat'],
                    null,
                    (object)[1, 2, 3],
                ],
            ],
        ];
    }

    /**
     * @param mixed $source
     * @param string $accessor
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @param mixed[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExtract_IterableMethod_Success')]
    public function testExtract_IterableMethod_Success(
        mixed $source,
        string $accessor,
        ?\ArrayAccess $context,
        array $expectedResult,
    ): void {
        $extractor = new Extractor();

        $result = $extractor->extract(
            source: $source,
            accessor: $accessor,
            context: $context,
        );
        $this->assertEquals($expectedResult, $result); // Because it contains an object
    }
}
