<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\PropertyAccessExceptionInterface;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Test\Fixture\TestArrayAccess;
use Klevu\Pipelines\Transformer\SetPropertyValue;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(SetPropertyValue::class)]
class SetPropertyValueTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = SetPropertyValue::class;
    /**
     * @var bool
     */
    protected bool $useStrictEqualityChecks = false;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Null(),
            self::dataProvider_testTransform_Valid_Array(),
            self::dataProvider_testTransform_Valid_ArrayAccess(),
            self::dataProvider_testTransform_Valid_stdClass(),
            self::dataProvider_testTransform_Valid_PropertySeparator(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Null(): array
    {
        return self::convertFixtures(
            fixtures: [
                'null 1 (assoc)' => [
                    null,
                    ['foo', 'bar', null, true],
                    ['foo' => 'bar'],
                ],
                'null 1 (obj)' => [
                    null,
                    ['foo', 'bar', null, false],
                    (object)['foo' => 'bar'],
                ],
                'null 2 (assoc)' => [
                    null,
                    ['foo.bar', 'baz', null, true],
                    ['foo' => ['bar' => 'baz']],
                ],
                'null 2 (obj)' => [
                    null,
                    ['foo.bar', 'baz', null, false],
                    (object)['foo' => (object)['bar' => 'baz']],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                'empty array 1 (assoc)' => [
                    [],
                    ['foo', 'bar', null, true],
                    ['foo' => 'bar'],
                ],
                'empty array 1 (obj)' => [
                    [],
                    ['foo', 'bar', null, false],
                    ['foo' => 'bar'],
                ],
                'empty array 2 (assoc)' => [
                    [],
                    ['foo.bar', 'baz', null, true],
                    ['foo' => ['bar' => 'baz']],
                ],
                'empty array 2 (obj)' => [
                    [],
                    ['foo.bar', 'baz', null, false],
                    ['foo' => (object)['bar' => 'baz']],
                ],
                'simple array 1 (assoc)' => [
                    ['foo' => 'bar'],
                    ['foo', 'baz', null, true],
                    ['foo' => 'baz'],
                ],
                'simple array 1 (obj)' => [
                    ['foo' => 'bar'],
                    ['foo', 'baz', null, false],
                    ['foo' => 'baz'],
                ],
                'simple array 2 (assoc)' => [
                    ['foo' => 'bar'],
                    ['wom', 'bat', null, true],
                    [
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
                'simple array 2 (obj)' => [
                    ['foo' => 'bar'],
                    ['wom', 'bat', null, false],
                    [
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
                'simple array 3 (assoc)' => [
                    ['foo' => 'bar'],
                    ['a.b', 'c', null, true],
                    [
                        'foo' => 'bar',
                        'a' => [
                            'b' => 'c',
                        ],
                    ],
                ],
                'simple array 3 (obj)' => [
                    ['foo' => 'bar'],
                    ['a.b', 'c', null, false],
                    [
                        'foo' => 'bar',
                        'a' => (object)[
                            'b' => 'c',
                        ],
                    ],
                ],
                'array with empty string key 1 (assoc)' => [
                    ['foo' => 'bar'],
                    ['', 'baz', null, true],
                    ['foo' => 'bar'],
                ],
                'array with empty string key 1 (obj)' => [
                    ['foo' => 'bar'],
                    ['', 'baz', null, false],
                    ['foo' => 'bar'],
                ],
                'array with empty string key 2 (assoc)' => [
                    ['foo' => ['bar']],
                    ['foo.', 'baz', null, true],
                    ['foo' => ['bar']],
                ],
                'array with empty string key 2 (obj)' => [
                    ['foo' => ['bar']],
                    ['foo.', 'baz', null, false],
                    ['foo' => ['bar']],
                ],
                'array with empty string key 3 (assoc)' => [
                    ['foo' => ['bar']],
                    ['wom.', 'bat', null, true],
                    ['foo' => ['bar'], 'wom' => []],
                ],
                'array with empty string key 3 (obj)' => [
                    ['foo' => ['bar']],
                    ['wom.', 'bat', null, false],
                    ['foo' => ['bar'], 'wom' => (object)[]],
                ],
                'array with empty string key 4 (assoc)' => [
                    ['foo' => ['bar']],
                    ['wom..ble', 'bat', null, true],
                    ['foo' => ['bar'], 'wom' => []],
                ],
                'array with empty string key 4 (obj)' => [
                    ['foo' => ['bar']],
                    ['wom..ble', 'bat', null, false],
                    ['foo' => ['bar'], 'wom' => (object)[]],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_ArrayAccess(): array
    {
        return self::convertFixtures(
            fixtures: [
                'empty ArrayAccess 1 (assoc)' => [
                    new TestArrayAccess([]),
                    ['foo', 'bar', null, true],
                    new TestArrayAccess(['foo' => 'bar']),
                ],
                'empty ArrayAccess 1 (obj)' => [
                    new TestArrayAccess([]),
                    ['foo', 'bar', null, false],
                    new TestArrayAccess(['foo' => 'bar']),
                ],
                'empty ArrayAccess 2 (assoc)' => [
                    new TestArrayAccess([]),
                    ['foo.bar', 'baz', null, true],
                    new TestArrayAccess(['foo' => ['bar' => 'baz']]),
                ],
                'empty ArrayAccess 2 (obj)' => [
                    new TestArrayAccess([]),
                    ['foo.bar', 'baz', null, false],
                    new TestArrayAccess(['foo' => (object)['bar' => 'baz']]),
                ],
                'simple ArrayAccess 1 (assoc)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['foo', 'baz', null, true],
                    new TestArrayAccess(['foo' => 'baz']),
                ],
                'simple ArrayAccess 1 (obj)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['foo', 'baz', null, false],
                    new TestArrayAccess(['foo' => 'baz']),
                ],
                'simple ArrayAccess 2 (assoc)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['wom', 'bat', null, true],
                    new TestArrayAccess([
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ]),
                ],
                'simple ArrayAccess 2 (obj)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['wom', 'bat', null, false],
                    new TestArrayAccess([
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ]),
                ],
                'simple ArrayAccess 3 (assoc)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['a.b', 'c', null, true],
                    new TestArrayAccess([
                        'foo' => 'bar',
                        'a' => [
                            'b' => 'c',
                        ],
                    ]),
                ],
                'simple ArrayAccess 3 (obj)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['a.b', 'c', null, false],
                    new TestArrayAccess([
                        'foo' => 'bar',
                        'a' => (object)[
                            'b' => 'c',
                        ],
                    ]),
                ],
                'ArrayAccess with empty string key 1 (assoc)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['', 'baz', null, true],
                    new TestArrayAccess(['foo' => 'bar']),
                ],
                'ArrayAccess with empty string key 1 (obj)' => [
                    new TestArrayAccess(['foo' => 'bar']),
                    ['', 'baz', null, false],
                    new TestArrayAccess(['foo' => 'bar']),
                ],
                'ArrayAccess with empty string key 2 (assoc)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['foo.', 'baz', null, true],
                    new TestArrayAccess(['foo' => ['bar']]),
                ],
                'ArrayAccess with empty string key 2 (obj)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['foo.', 'baz', null, false],
                    new TestArrayAccess(['foo' => ['bar']]),
                ],
                'ArrayAccess with empty string key 3 (assoc)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['wom.', 'bat', null, true],
                    new TestArrayAccess(['foo' => ['bar'], 'wom' => []]),
                ],
                'ArrayAccess with empty string key 3 (obj)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['wom.', 'bat', null, false],
                    new TestArrayAccess(['foo' => ['bar'], 'wom' => (object)[]]),
                ],
                'ArrayAccess with empty string key 4 (assoc)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['wom..ble', 'bat', null, true],
                    new TestArrayAccess(['foo' => ['bar'], 'wom' => []]),
                ],
                'ArrayAccess with empty string key 4 (obj)' => [
                    new TestArrayAccess(['foo' => ['bar']]),
                    ['wom..ble', 'bat', null, false],
                    new TestArrayAccess(['foo' => ['bar'], 'wom' => (object)[]]),
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_stdClass(): array
    {
        return self::convertFixtures(
            fixtures: [
                'empty stdClass 1 (assoc)' => [
                    (object)[],
                    ['foo', 'bar', null, true],
                    (object)['foo' => 'bar'],
                ],
                'empty stdClass 1 (obj)' => [
                    (object)[],
                    ['foo', 'bar', null, false],
                    (object)['foo' => 'bar'],
                ],
                'empty stdClass 2 (assoc)' => [
                    (object)[],
                    ['foo.bar', 'baz', null, true],
                    (object)['foo' => ['bar' => 'baz']],
                ],
                'empty stdClass 2 (obj)' => [
                    (object)[],
                    ['foo.bar', 'baz', null, false],
                    (object)['foo' => (object)['bar' => 'baz']],
                ],
                'simple stdClass 1 (assoc)' => [
                    (object)['foo' => 'bar'],
                    ['foo', 'baz', null, true],
                    (object)['foo' => 'baz'],
                ],
                'simple stdClass 1 (obj)' => [
                    (object)['foo' => 'bar'],
                    ['foo', 'baz', null, false],
                    (object)['foo' => 'baz'],
                ],
                'simple stdClass 2 (assoc)' => [
                    (object)['foo' => 'bar'],
                    ['wom', 'bat', null, true],
                    (object)[
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
                'simple stdClass 2 (obj)' => [
                    (object)['foo' => 'bar'],
                    ['wom', 'bat', null, false],
                    (object)[
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
                'simple stdClass 3 (assoc)' => [
                    (object)['foo' => 'bar'],
                    ['a.b', 'c', null, true],
                    (object)[
                        'foo' => 'bar',
                        'a' => [
                            'b' => 'c',
                        ],
                    ],
                ],
                'simple stdClass 3 (obj)' => [
                    (object)['foo' => 'bar'],
                    ['a.b', 'c', null, false],
                    (object)[
                        'foo' => 'bar',
                        'a' => (object)[
                            'b' => 'c',
                        ],
                    ],
                ],
                'stdClass with empty string key 1 (assoc)' => [
                    (object)['foo' => 'bar'],
                    ['', 'baz', null, true],
                    (object)['foo' => 'bar'],
                ],
                'stdClass with empty string key 1 (obj)' => [
                    (object)['foo' => 'bar'],
                    ['', 'baz', null, false],
                    (object)['foo' => 'bar'],
                ],
                'stdClass with empty string key 2 (assoc)' => [
                    (object)['foo' => ['bar']],
                    ['foo.', 'baz', null, true],
                    (object)['foo' => ['bar']],
                ],
                'stdClass with empty string key 2 (obj)' => [
                    (object)['foo' => ['bar']],
                    ['foo.', 'baz', null, false],
                    (object)['foo' => ['bar']],
                ],
                'stdClass with empty string key 3 (assoc)' => [
                    (object)['foo' => ['bar']],
                    ['wom.', 'bat', null, true],
                    (object)['foo' => ['bar'], 'wom' => []],
                ],
                'stdClass with empty string key 3 (obj)' => [
                    (object)['foo' => ['bar']],
                    ['wom.', 'bat', null, false],
                    (object)['foo' => ['bar'], 'wom' => (object)[]],
                ],
                'stdClass with empty string key 4 (assoc)' => [
                    (object)['foo' => ['bar']],
                    ['wom..ble', 'bat', null, true],
                    (object)['foo' => ['bar'], 'wom' => []],
                ],
                'stdClass with empty string key 4 (obj)' => [
                    (object)['foo' => ['bar']],
                    ['wom..ble', 'bat', null, false],
                    (object)['foo' => ['bar'], 'wom' => (object)[]],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_PropertySeparator(): array
    {
        return self::convertFixtures(
            fixtures: [
                'array:property separator 1 (assoc)' => [
                    [],
                    ['foo.bar', 'baz', '/', true],
                    ['foo.bar' => 'baz'],
                ],
                'array:property separator 1 (object)' => [
                    [],
                    ['foo.bar', 'baz', '/', false],
                    ['foo.bar' => 'baz'],
                ],
                'array:property separator 2 (assoc)' => [
                    ['foo' => 'bar'],
                    ['foo.bar', 'baz', '/', true],
                    [
                        'foo' => 'bar',
                        'foo.bar' => 'baz',
                    ],
                ],
                'array:property separator 2 (object)' => [
                    ['foo' => 'bar'],
                    ['foo.bar', 'baz', '/', false],
                    [
                        'foo' => 'bar',
                        'foo.bar' => 'baz',
                    ],
                ],
                'array:property separator 3 (assoc)' => [
                    [],
                    ['foo.bar/baz', 'a', '/', true],
                    ['foo.bar' => ['baz' => 'a']],
                ],
                'array:property separator 3 (object)' => [
                    [],
                    ['foo.bar/baz', 'a', '/', false],
                    ['foo.bar' => (object)['baz' => 'a']],
                ],
                'array:property separator 4 (assoc)' => [
                    ['foo.bar' => ['wom' => 'bat']],
                    ['foo.bar/baz', 'a', '/', true],
                    [
                        'foo.bar' => [
                            'wom' => 'bat',
                            'baz' => 'a',
                        ],
                    ],
                ],
                'array:property separator 4 (object)' => [
                    ['foo.bar' => ['wom' => 'bat']],
                    ['foo.bar/baz', 'a', '/', true],
                    [
                        'foo.bar' => [
                            'wom' => 'bat',
                            'baz' => 'a',
                        ],
                    ],
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
                // Property key
                array_map(
                    callback: static fn ($propertyKeyValue): array => [
                        ['foo' => 'bar'],
                        [$propertyKeyValue, null],
                        '',
                    ],
                    array: [
                        3.14,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                // Path separator
                array_map(
                    callback: static fn ($propertyPathSeparatorValue): array => [
                        ['foo' => 'bar'],
                        [null, null, $propertyPathSeparatorValue],
                        '',
                    ],
                    array: [
                        42,
                        3.14,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                // Associative
                array_map(
                    callback: static fn ($associativeValue): array => [
                        ['foo' => 'bar'],
                        [null, null, null, $associativeValue],
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
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_ThrowsException_InvalidDataTypeForChild(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['foo' => 'bar'],
                    ['foo.bar', 'baz', null, true],
                    [
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
                [
                    ['foo' => 'bar'],
                    ['foo.', 'baz', null, true],
                    [
                        'foo' => 'bar',
                        'wom' => 'bat',
                    ],
                ],
            ],
        );
    }

    /**
     * @param mixed $data
     * @param mixed $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     * @throws PropertyAccessExceptionInterface
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ThrowsException_InvalidDataTypeForChild')]
    public function testTransform_ThrowsException_InvalidDataTypeForChild(
        mixed $data,
        mixed $expectedResult, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var SetPropertyValue $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidInputDataException::class);
        $transformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
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
                        SetPropertyValue::ARGUMENT_INDEX_PROPERTY_KEY => $data[1][0] ?? null,
                        SetPropertyValue::ARGUMENT_INDEX_PROPERTY_VALUE => $data[1][1] ?? null,
                        SetPropertyValue::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR => $data[1][2] ?? null,
                        SetPropertyValue::ARGUMENT_INDEX_ASSOCIATIVE => $data[1][3] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
