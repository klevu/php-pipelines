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
use Klevu\Pipelines\Test\Fixture\TestArrayAccess;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Transformer\Flatten;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test with constructor args
 * @todo Test extractions
 */
#[CoversClass(Flatten::class)]
class FlattenTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Flatten::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Iterator(),
            self::dataProvider_testTransform_Valid_RetainKeys(),
            self::dataProvider_testTransform_Valid_Iterator_RetainKeys(),
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
                [null, [false], null],

                [
                    [
                        ['foo'],
                        ['bar'],
                        ['baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        ['foo', 'bar', 'baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    [
                        'a' => ['foo'],
                        'b' => ['bar'],
                        'c' => ['baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        'a' => ['z' => 'foo'],
                        'b' => ['z' => 'bar'],
                        'c' => ['z' => 'baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['b' => 'bar'],
                        ['c' => 'baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['a' => 'bar'],
                        ['a' => 'baz'],
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        [2 => 'foo'],
                        [1 => 'bar'],
                        [0 => 'baz'],
                    ],
                    [true],
                    [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
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
                    new TestIterator([
                        ['foo'],
                        ['bar'],
                        ['baz'],
                    ]),
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        ['foo', 'bar', 'baz'],
                    ]),
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    new TestIterator([
                        'a' => ['foo'],
                        'b' => ['bar'],
                        'c' => ['baz'],
                    ]),
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        'a' => ['z' => 'foo'],
                        'b' => ['z' => 'bar'],
                        'c' => ['z' => 'baz'],
                    ]),
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        ['a' => 'foo'],
                        ['b' => 'bar'],
                        ['c' => 'baz'],
                    ]),
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    [
                        new TestIterator(['foo']),
                        new TestIterator(['bar']),
                        new TestIterator(['baz']),
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestIterator(['foo', 'bar', 'baz']),
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    [
                        'a' => new TestIterator(['foo']),
                        'b' => new TestIterator(['bar']),
                        'c' => new TestIterator(['baz']),
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestIterator(['a' => 'foo']),
                        new TestIterator(['b' => 'bar']),
                        new TestIterator(['c' => 'baz']),
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestIterator(['a' => 'foo']),
                        new TestIterator(['a' => 'bar']),
                        new TestIterator(['a' => 'baz']),
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        new TestIterator(['a' => 'foo']),
                        new TestIterator(['a' => 'bar']),
                        new TestIterator(['a' => 'baz']),
                    ]),
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        'a' => new TestIterator(['a' => 'foo']),
                        'b' => new TestIterator(['a' => 'bar']),
                        'c' => new TestIterator(['a' => 'baz']),
                    ]),
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestIterator([2 => 'foo']),
                        new TestIterator([1 => 'bar']),
                        new TestIterator([0 => 'baz']),
                    ],
                    [true],
                    [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_RetainKeys(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [true], null],

                [
                    [
                        ['foo'],
                        ['bar'],
                        ['baz'],
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        ['foo', 'bar', 'baz'],
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    [
                        'a' => ['foo'],
                        'b' => ['bar'],
                        'c' => ['baz'],
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        'a' => ['z' => 'foo'],
                        'b' => ['z' => 'bar'],
                        'c' => ['z' => 'baz'],
                    ],
                    [true],
                    [
                        'z' => 'baz',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['b' => 'bar'],
                        ['c' => 'baz'],
                    ],
                    [true],
                    [
                        'a' => 'foo',
                        'b' => 'bar',
                        'c' => 'baz',
                    ],
                ],
                [
                    [
                        ['a' => 'foo'],
                        ['a' => 'bar'],
                        ['a' => 'baz'],
                    ],
                    [true],
                    [
                        'a' => 'baz',
                    ],
                ],
                [
                    [
                        [2 => 'foo'],
                        [1 => 'bar'],
                        [0 => 'baz'],
                    ],
                    [true],
                    // PHP's merge doesn't respect integer keys for overwriting existing values
                    //  We could force this by casting to string keys, but then we wouldn't be
                    //  returning the same keys anyway
                    [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Iterator_RetainKeys(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    new TestIterator([
                        ['foo'],
                        ['bar'],
                        ['baz'],
                    ]),
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        ['foo', 'bar', 'baz'],
                    ]),
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    new TestIterator([
                        'a' => ['foo'],
                        'b' => ['bar'],
                        'c' => ['baz'],
                    ]),
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    new TestIterator([
                        'a' => ['z' => 'foo'],
                        'b' => ['z' => 'bar'],
                        'c' => ['z' => 'baz'],
                    ]),
                    [true],
                    [
                        'z' => 'baz',
                    ],
                ],
                [
                    new TestIterator([
                        ['a' => 'foo'],
                        ['b' => 'bar'],
                        ['c' => 'baz'],
                    ]),
                    [true],
                    [
                        'a' => 'foo',
                        'b' => 'bar',
                        'c' => 'baz',
                    ],
                ],

                [
                    [
                        new TestIterator(['foo']),
                        new TestIterator(['bar']),
                        new TestIterator(['baz']),
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestIterator(['foo', 'bar', 'baz']),
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],

                [
                    [
                        'a' => new TestIterator(['foo']),
                        'b' => new TestIterator(['bar']),
                        'c' => new TestIterator(['baz']),
                    ],
                    [true],
                    [
                        'foo',
                        'bar',
                        'baz',
                    ],
                ],
                [
                    [
                        new TestArrayAccess(['a' => 'foo']),
                        new TestArrayAccess(['b' => 'bar']),
                        new TestArrayAccess(['c' => 'baz']),
                    ],
                    [true],
                    [
                        'a' => 'foo',
                        'b' => 'bar',
                        'c' => 'baz',
                    ],
                ],
                [
                    [
                        new TestArrayAccess(['a' => 'foo']),
                        new TestArrayAccess(['a' => 'bar']),
                        new TestArrayAccess(['a' => 'baz']),
                    ],
                    [true],
                    [
                        'a' => 'baz',
                    ],
                ],
                [
                    new TestIterator([
                        new TestArrayAccess(['a' => 'foo']),
                        new TestArrayAccess(['a' => 'bar']),
                        new TestArrayAccess(['a' => 'baz']),
                    ]),
                    [true],
                    [
                        'a' => 'baz',
                    ],
                ],
                [
                    new TestIterator([
                        'a' => new TestArrayAccess(['a' => 'foo']),
                        'b' => new TestArrayAccess(['a' => 'bar']),
                        'c' => new TestArrayAccess(['a' => 'baz']),
                    ]),
                    [true],
                    [
                        'a' => 'baz',
                    ],
                ],
                [
                    [
                        new TestArrayAccess([2 => 'foo']),
                        new TestArrayAccess([1 => 'bar']),
                        new TestArrayAccess([0 => 'baz']),
                    ],
                    [true],
                    // PHP's merge doesn't respect integer keys for overwriting existing values
                    //  We could force this by casting to string keys, but then we wouldn't be
                    //  returning the same keys anyway
                    [
                        0 => 'foo',
                        1 => 'bar',
                        2 => 'baz',
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
            [['foo']],
            [[42]],
            [[3.14]],
            [[false]],
            [[(object)['foo']]],
            [[$fileHandle]],
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
                    callback: static fn ($retainKeysArgumentValue): array => [
                        [
                            ['foo'],
                        ],
                        [$retainKeysArgumentValue],
                        null,
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
                        Flatten::ARGUMENT_INDEX_RETAIN_KEYS => $data[1][0] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
