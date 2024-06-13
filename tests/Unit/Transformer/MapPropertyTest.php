<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Test\Fixture\TestObject;
use Klevu\Pipelines\Transformer\MapProperty;
use Klevu\Pipelines\Transformer\TransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test logging on failed extraction
 * @todo Test context extractions
 * @todo Test invalid constructor args
 */
#[CoversClass(MapProperty::class)]
class MapPropertyTest extends AbstractTransformerTestCase
{
    protected string $transformerFqcn = MapProperty::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Simple_ReturnNullOnFailedExtraction(),
            self::dataProvider_testTransform_Valid_Iterator(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    [
                        new TestObject(
                            publicProperty: 'baz',
                        ),
                        ['publicProperty' => 'bar'],
                        ['publicProperty' => 'abc'],
                    ],
                    [
                        'publicProperty',
                    ],
                    [
                        'baz',
                        'bar',
                        'abc',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_ReturnNullOnFailedExtraction(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    [
                        new TestObject(
                            publicProperty: 'baz',
                        ),
                        ['publicProperty' => 'bar'],
                        ['publicProperty' => 'abc'],
                    ],
                    [
                        'publicProperty',
                        true,
                    ],
                    [
                        'baz',
                        'bar',
                        'abc',
                    ],
                ],
                [
                    [
                        new TestObject(
                            privateProperty: 'baz',
                        ),
                        ['publicProperty' => 'bar'],
                        ['publicProperty' => 'abc'],
                    ],
                    [
                        'publicProperty',
                    ],
                    [
                        null,
                        'bar',
                        'abc',
                    ],
                ],
                [
                    [
                        new TestObject(
                            publicProperty: 'baz',
                        ),
                        ['foo' => 'bar'],
                        ['publicProperty' => 'abc'],
                    ],
                    [
                        'publicProperty',
                        true,
                    ],
                    [
                        'baz',
                        null,
                        'abc',
                    ],
                ],
                [
                    [
                        new TestObject(
                            privateProperty: 'baz',
                        ),
                    ],
                    [
                        'getPrivateProperty()',
                    ],
                    [
                        'baz',
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
                // TestIterator
                // TestArrayAccess
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
            [(object)['foo']],
            [$fileHandle],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return self::convertFixtures(
            fixtures: array_merge(
                array_map(
                    callback: static fn ($accessorArgumentValue): array => [
                        [['foo' => 'bar']],
                        [$accessorArgumentValue, null],
                        '',
                    ],
                    array: [
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($returnNullOnFailedExtractionArgumentValue): array => [
                        [['foo' => 'bar']],
                        ['foo', $returnNullOnFailedExtractionArgumentValue],
                        '',
                    ],
                    array: [
                        'foo',
                        42,
                        3.14,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
            ),
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

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[2] ?? null,
                is_array($data[1] ?? null)
                    ? $argumentIteratorFactory->create([
                        MapProperty::ARGUMENT_INDEX_ACCESSOR => $data[1][0] ?? null,
                        MapProperty::ARGUMENT_INDEX_RETURN_NULL_ON_FAILED_EXTRACTION => $data[1][1] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_FailedExtraction(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [
                        new TestObject(
                            publicProperty: 'baz',
                        ),
                        ['privateProperty' => 'bar'],
                        ['publicProperty' => 'abc'],
                    ],
                    [
                        'publicProperty',
                    ],
                    [],
                ],
                [
                    [
                        new TestObject(
                            privateProperty: 'baz',
                        ),
                        [
                            'privateProperty' => 'wom',
                        ],
                    ],
                    [
                        'getPrivateProperty()',
                    ],
                    [],
                ],
            ],
        );
    }

    /**
     * @param mixed $data
     * @param mixed $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_FailedExtraction')]
    public function testTransform_InvalidInputData_FailedExtraction(
        mixed $data,
        mixed $expectedResult, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidInputDataException::class);
        $transformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
    }
}
