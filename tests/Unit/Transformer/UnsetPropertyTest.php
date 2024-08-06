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
use Klevu\Pipelines\Test\Fixture\TestArrayAccess;
use Klevu\Pipelines\Transformer\UnsetProperty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

#[CoversClass(UnsetProperty::class)]
class UnsetPropertyTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = UnsetProperty::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Array(),
            self::dataProvider_testTransform_Valid_ArrayAccess(),
            self::dataProvider_testTransform_Valid_stdClass(),
            self::dataProvider_testTransform_Valid_PropertySeparator(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                'simple 1' => [null, [], null],
                'simple 2' => [null, ['foo'], null],
                'simple 3' => [null, ['foo.bar'], null],
                'simple 4' => [null, ['foo.bar', '/'], null],
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
                'array:single 1' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo'],
                    [
                        'wom' => 'bat',
                    ],
                ],
                'array:single 2' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['wom'],
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ],
                ],
                'array:single 3' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['baz'],
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'array:single, numeric as int 1' => [
                    [
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    [0],
                    [
                        1 => 'bat',
                    ],
                ],
                'array:single, numeric as string' => [
                    [
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    ['0'],
                    [
                        1 => 'bat',
                    ],
                ],
                'array:single, numeric as int 2' => [
                    [
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    [1],
                    [
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ],
                ],
                'array:nested (2) 1' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar'],
                    [
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'array:nested (3) 1' => [
                    [
                        'foo' => [
                            'bar' => [
                                'foo' => [
                                    'zig' => 'zag',
                                ],
                            ],
                        ],
                    ],
                    ['foo.bar.foo'],
                    [
                        'foo' => [
                            'bar' => [],
                        ],
                    ],
                ],
                'array:nested (2) with numeric' => [
                    [
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    ['0.bar'],
                    [
                        [
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                ],
                'array:nested (3) numeric' => [
                    [
                        [
                            [
                                [
                                    'foo',
                                ],
                                [
                                    'bar',
                                ],
                            ],
                            [
                                'baz',
                            ],
                        ],
                        [
                            'wom',
                        ],
                    ],
                    ['0.0.1'],
                    [
                        [
                            [
                                [
                                    'foo',
                                ],
                            ],
                            [
                                'baz',
                            ],
                        ],
                        [
                            'wom',
                        ],
                    ],
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
                'arrayAccess:single 1' => [
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                    ['foo'],
                    new TestArrayAccess([
                        'wom' => 'bat',
                    ]),
                ],
                'arrayAccess:single 2' => [
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                    ['wom'],
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ]),
                ],
                'arrayAccess:single 3' => [
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                    ['baz'],
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                ],
                'arrayAccess:single, numeric as int 1' => [
                    new TestArrayAccess(
                        data: [
                            [
                                'bar' => 'baz',
                                'zig' => 'zag',
                            ],
                            'bat',
                        ],
                        retainNumericKeys: true,
                    ),
                    [0],
                    new TestArrayAccess(
                        data: [
                            1 => 'bat',
                        ],
                        retainNumericKeys: true,
                    ),
                ],
                'arrayAccess:single, numeric as string' => [
                    new TestArrayAccess(
                        data: [
                            [
                                'bar' => 'baz',
                                'zig' => 'zag',
                            ],
                            'bat',
                        ],
                        retainNumericKeys: true,
                    ),
                    ['0'],
                    new TestArrayAccess(
                        data: [
                            1 => 'bat',
                        ],
                        retainNumericKeys: true,
                    ),
                ],
                'arrayAccess:single, numeric as int 2' => [
                    new TestArrayAccess([
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ]),
                    [1],
                    new TestArrayAccess([
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ]),
                ],
                'arrayAccess:nested (2) 1' => [
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                    ['foo.bar'],
                    new TestArrayAccess([
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ]),
                ],
                'arrayAccess:nested (3) 1' => [
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => [
                                'foo' => [
                                    'zig' => 'zag',
                                ],
                            ],
                        ],
                    ]),
                    ['foo.bar.foo'],
                    new TestArrayAccess([
                        'foo' => [
                            'bar' => [],
                        ],
                    ]),
                ],
                'arrayAccess:nested (2) with numeric' => [
                    new TestArrayAccess([
                        [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ]),
                    ['0.bar'],
                    new TestArrayAccess([
                        [
                            'zig' => 'zag',
                        ],
                        'bat',
                    ]),
                ],
                'arrayAccess:nested (3) numeric' => [
                    new TestArrayAccess([
                        [
                            [
                                [
                                    'foo',
                                ],
                                [
                                    'bar',
                                ],
                            ],
                            [
                                'baz',
                            ],
                        ],
                        [
                            'wom',
                        ],
                    ]),
                    ['0.0.1'],
                    new TestArrayAccess([
                        [
                            [
                                [
                                    'foo',
                                ],
                            ],
                            [
                                'baz',
                            ],
                        ],
                        [
                            'wom',
                        ],
                    ]),
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
                'stdClass:single 1' => [
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo'],
                    (object)[
                        'wom' => 'bat',
                    ],
                ],
                'stdClass:single 2' => [
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['wom'],
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ],
                ],
                'stdClass:single 3' => [
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['baz'],
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'stdClass:single, numeric as int 1' => [
                    (object)[
                        (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    [0],
                    (object)[
                        1 => 'bat',
                    ],
                ],
                'stdClass:single, numeric as string' => [
                    (object)[
                        (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    ['0'],
                    (object)[
                        1 => 'bat',
                    ],
                ],
                'stdClass:single, numeric as int 2' => [
                    (object)[
                        (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    [1],
                    (object)[
                        (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                    ],
                ],
                'stdClass:nested (2) 1' => [
                    (object)[
                        'foo' => (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar'],
                    (object)[
                        'foo' => (object)[
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'stdClass:nested (3) 1' => [
                    (object)[
                        'foo' => (object)[
                            'bar' => (object)[
                                'foo' => (object)[
                                    'zig' => 'zag',
                                ],
                            ],
                        ],
                    ],
                    ['foo.bar.foo'],
                    (object)[
                        'foo' => (object)[
                            'bar' => (object)[],
                        ],
                    ],
                ],
                'stdClass:nested (2) with numeric' => [
                    (object)[
                        (object)[
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                    ['0.bar'],
                    (object)[
                        (object)[
                            'zig' => 'zag',
                        ],
                        'bat',
                    ],
                ],
                'stdClass:nested (3) numeric' => [
                    (object)[
                        (object)[
                            (object)[
                                (object)[
                                    'foo',
                                ],
                                (object)[
                                    'bar',
                                ],
                            ],
                            (object)[
                                'baz',
                            ],
                        ],
                        (object)[
                            'wom',
                        ],
                    ],
                    ['0.0.1'],
                    (object)[
                        (object)[
                            (object)[
                                (object)[
                                    'foo',
                                ],
                            ],
                            (object)[
                                'baz',
                            ],
                        ],
                        (object)[
                            'wom',
                        ],
                    ],
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
            fixtures: [ // @phpstan-ignore-line
                'array:property separator (1)' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '.'],
                    [
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'array:property separator (2)' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '/'],
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'array:property separator (3)' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo/bar', '/'],
                    [
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'arrayAccess:property separator (1)' => new TestArrayAccess([
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '.'],
                    [
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                ]),
                'arrayAccess:property separator (2)' => new TestArrayAccess([
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '/'],
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ]),
                'arrayAccess:property separator (3)' => new TestArrayAccess([
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo/bar', '/'],
                    [
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                ]),
                'stdClass:property separator (1)' => [
                    (object)[
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '.'],
                    (object)[
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'stdClass:property separator (2)' => [
                    (object)[
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo.bar', '/'],
                    (object)[
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                ],
                'stdClass:property separator (3)' => [
                    (object)[
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
                    ],
                    ['foo/bar', '/'],
                    (object)[
                        'foo' => [
                            'zig' => 'zag',
                        ],
                        'foo.bar' => [
                            'baz' => [
                                'wom.bat',
                            ],
                        ],
                        'wom' => 'bat',
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
                array_map(
                    callback: static fn ($propertyPathSeparatorValue): array => [
                        ['foo' => 'bar'],
                        [null, $propertyPathSeparatorValue],
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
                'array:nested (2) 2' => [
                    [
                        'foo' => [
                            'bar' => 'baz',
                            'zig' => 'zag',
                        ],
                        'wom' => 'bat',
                    ],
                    ['wom.bat'],
                    '',
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
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ThrowsException_InvalidDataTypeForChild')]
    public function testTransform_ThrowsException_InvalidDataTypeForChild(
        mixed $data,
        mixed $expectedResult, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var UnsetProperty $transformer */
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
                        UnsetProperty::ARGUMENT_INDEX_PROPERTY_KEY => $data[1][0] ?? null,
                        UnsetProperty::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR => $data[1][1] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
