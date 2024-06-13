<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\Unique;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(Unique::class)]
class UniqueTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Unique::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Strict(),
            self::dataProvider_testTransform_Valid_NotRetainKeys(),
            self::dataProvider_testTransform_Valid_ContainsObjects(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [],
                    null,
                ],
                [
                    [],
                    [],
                    [],
                ],
                [
                    [null],
                    [],
                    [null],
                ],

                [
                    ['foo', 'bar', 'baz'],
                    [],
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ['foo', ' foo', 'foo ', ' foo '],
                    [],
                    ['foo', ' foo', 'foo ', ' foo '],
                ],
                [
                    ['foo', 'FOO', 'fOo'],
                    [],
                    ['foo', 'FOO', 'fOo'],
                ],
                [
                    ['a' => 'b', 'c' => 'd'],
                    [],
                    ['a' => 'b', 'c' => 'd'],
                ],

                [
                    [
                        ['foo'],
                        ['foo'],
                        'bar',
                    ],
                    [],
                    [
                        0 => ['foo'],
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                    [],
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                ],
                [
                    [
                        42,
                        '42',
                        'foo',
                        42,
                        42.0,
                    ],
                    [],
                    [
                        0 => 42,
                        2 => 'foo',
                    ],
                ],
                [
                    [
                        10 => 42,
                        9 => '42',
                        8 => 'foo',
                        7 => 42,
                        6 => 42.0,
                    ],
                    [],
                    [
                        10 => 42,
                        8 => 'foo',
                    ],
                ],
                [
                    [
                        'foo' => 42,
                        'bar' => '42',
                        'baz' => 'foo',
                        'wom' => 42,
                        'bat' => 42.0,
                    ],
                    [],
                    [
                        'foo' => 42,
                        'baz' => 'foo',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Strict(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [true],
                    null,
                ],
                [
                    [],
                    [true],
                    [],
                ],
                [
                    [null],
                    [true],
                    [null],
                ],

                [
                    ['foo', 'bar', 'baz'],
                    [true],
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ['foo', ' foo', 'foo ', ' foo '],
                    [true],
                    ['foo', ' foo', 'foo ', ' foo '],
                ],
                [
                    ['foo', 'FOO', 'fOo'],
                    [true],
                    ['foo', 'FOO', 'fOo'],
                ],
                [
                    ['a' => 'b', 'c' => 'd'],
                    [true],
                    ['a' => 'b', 'c' => 'd'],
                ],

                [
                    [
                        ['foo'],
                        ['foo'],
                        'bar',
                    ],
                    [true],
                    [
                        0 => ['foo'],
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                    [true],
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                ],
                [
                    [
                        42,
                        '42',
                        'foo',
                        42,
                        42.0,
                    ],
                    [true],
                    [
                        0 => 42,
                        1 => '42',
                        2 => 'foo',
                        4 => 42.0,
                    ],
                ],
                [
                    [
                        10 => 42,
                        9 => '42',
                        8 => 'foo',
                        7 => 42,
                        6 => 42.0,
                    ],
                    [true],
                    [
                        10 => 42,
                        9 => '42',
                        8 => 'foo',
                        6 => 42.0,
                    ],
                ],
                [
                    [
                        'foo' => 42,
                        'bar' => '42',
                        'baz' => 'foo',
                        'wom' => 42,
                        'bat' => 42.0,
                    ],
                    [true],
                    [
                        'foo' => 42,
                        'bar' => '42',
                        'baz' => 'foo',
                        'bat' => 42.0,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_NotRetainKeys(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [true, false],
                    null,
                ],
                [
                    [],
                    [true, false],
                    [],
                ],
                [
                    [null],
                    [true, false],
                    [null],
                ],

                [
                    ['foo', 'bar', 'baz'],
                    [true, false],
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ['foo', ' foo', 'foo ', ' foo '],
                    [true, false],
                    ['foo', ' foo', 'foo ', ' foo '],
                ],
                [
                    ['foo', 'FOO', 'fOo'],
                    [true, false],
                    ['foo', 'FOO', 'fOo'],
                ],
                [
                    ['a' => 'b', 'c' => 'd'],
                    [true, false],
                    [0 => 'b', 1 => 'd'],
                ],

                [
                    [
                        ['foo'],
                        ['foo'],
                        'bar',
                    ],
                    [true, false],
                    [
                        0 => ['foo'],
                        1 => 'bar',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                    [true, false],
                    [
                        ['a' => 'foo'],
                        ['b' => 'foo'],
                        'bar',
                    ],
                ],
                [
                    [
                        42,
                        '42',
                        'foo',
                        42,
                        42.0,
                    ],
                    [true, false],
                    [
                        0 => 42,
                        1 => '42',
                        2 => 'foo',
                        3 => 42.0,
                    ],
                ],
                [
                    [
                        10 => 42,
                        9 => '42',
                        8 => 'foo',
                        7 => 42,
                        6 => 42.0,
                    ],
                    [true, false],
                    [
                        0 => 42,
                        1 => '42',
                        2 => 'foo',
                        3 => 42.0,
                    ],
                ],
                [
                    [
                        'foo' => 42,
                        'bar' => '42',
                        'baz' => 'foo',
                        'wom' => 42,
                        'bat' => 42.0,
                    ],
                    [true, false],
                    [
                        0 => 42,
                        1 => '42',
                        2 => 'foo',
                        3 => 42.0,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_ContainsObjects(): array
    {
        $objectFoo1 = (object)['foo'];
        $objectFoo2 = (object)['foo'];

        $object42String = (object)['foo' => '42'];
        $object42Int = (object)['foo' => 42];

        return self::convertFixtures(
            fixtures: [
                [
                    [
                        $objectFoo1,
                        $objectFoo2,
                        'bar',
                    ],
                    [],
                    [
                        0 => $objectFoo1,
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        $objectFoo1,
                        $objectFoo2,
                        'bar',
                    ],
                    [false, true],
                    [
                        0 => $objectFoo1,
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        $object42String,
                        $object42Int,
                        'bar',
                    ],
                    [false, true],
                    [
                        0 => $object42String,
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        1 => $objectFoo1,
                        0 => $objectFoo2,
                        2 => 'bar',
                    ],
                    [true, true],
                    [
                        1 => $objectFoo1,
                        0 => $objectFoo2,
                        2 => 'bar',
                    ],
                ],
                [
                    [
                        $object42String,
                        $object42Int,
                        'bar',
                    ],
                    [true, true],
                    [
                        $object42String,
                        $object42Int,
                        'bar',
                    ],
                ],
                [
                    [
                        $objectFoo1,
                        $objectFoo2,
                        'bar',
                    ],
                    [false, false],
                    [
                        $objectFoo1,
                        'bar',
                    ],
                ],
                [
                    [
                        1 => $objectFoo1,
                        0 => $objectFoo2,
                        2 => 'bar',
                    ],
                    [true, false],
                    [
                        0 => $objectFoo1,
                        1 => $objectFoo2,
                        2 => 'bar',
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
            [false],
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
                    callback: static fn ($strictArgumentValue): array => [
                        ['foo'],
                        [$strictArgumentValue, null],
                        [''],
                    ],
                    array: [
                        '',
                        0,
                        42,
                        3.14,
                        [true],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($retainKeysArgumentValue): array => [
                        ['foo'],
                        [null, $retainKeysArgumentValue],
                        [''],
                    ],
                    array: [
                        '',
                        0,
                        42,
                        3.14,
                        [true],
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
                        Unique::ARGUMENT_INDEX_STRICT => $data[1][0] ?? null,
                        Unique::ARGUMENT_INDEX_RETAIN_KEYS => $data[1][1] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
