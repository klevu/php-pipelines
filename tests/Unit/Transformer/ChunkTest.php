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
use Klevu\Pipelines\Transformer\Chunk;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor args
 */
#[CoversClass(Chunk::class)]
class ChunkTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Chunk::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [],
                    1,
                ],
                [
                    ['foo', 'bar'],
                    [
                        ['foo'],
                        ['bar'],
                    ],
                    1,
                ],
                [
                    ['foo', 'bar'],
                    [
                        ['foo', 'bar'],
                    ],
                    1000,
                ],
                [
                    array_fill(
                        start_index: 0,
                        count: 1000,
                        value: 'foo',
                    ),
                    [
                        array_fill(
                            start_index: 0,
                            count: 1000,
                            value: 'foo',
                        ),
                    ],
                    1000,
                ],
                [
                    array_fill(
                        start_index: 0,
                        count: 949,
                        value: 'foo',
                    ),
                    [
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 199,
                            value: 'foo',
                        ),
                    ],
                    250,
                ],
                [
                    array_fill(
                        start_index: 0,
                        count: 949,
                        value: 'foo',
                    ),
                    [
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 0,
                            count: 199,
                            value: 'foo',
                        ),
                    ],
                    250,
                    false,
                ],
                [
                    array_fill(
                        start_index: 0,
                        count: 949,
                        value: 'foo',
                    ),
                    [
                        array_fill(
                            start_index: 0,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 250,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 500,
                            count: 250,
                            value: 'foo',
                        ),
                        array_fill(
                            start_index: 750,
                            count: 199,
                            value: 'foo',
                        ),
                    ],
                    250,
                    true,
                ],
                [
                    [
                        'foo' => 1,
                        'bar' => 2,
                        'baz' => [
                            'wom' => 'bat',
                        ],
                    ],
                    [
                        [1],
                        [2],
                        [['wom' => 'bat']],
                    ],
                    1,
                ],
                [
                    [
                        'foo' => 1,
                        'bar' => 2,
                        'baz' => [
                            'wom' => 'bat',
                        ],
                    ],
                    [
                        [1],
                        [2],
                        [['wom' => 'bat']],
                    ],
                    1,
                    false,
                ],
                [
                    [
                        'foo' => 1,
                        'bar' => 2,
                        'baz' => [
                            'wom' => 'bat',
                        ],
                    ],
                    [
                        ['foo' => 1],
                        ['bar' => 2],
                        [
                            'baz' => ['wom' => 'bat'],
                        ],
                    ],
                    1,
                    true,
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
        return array_merge(
            self::dataProvider_testTransform_InvalidArguments_Length(),
            self::dataProvider_testTransform_InvalidArguments_PreserveKeys(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_InvalidArguments_Length(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return self::convertFixtures(
            fixtures: array_map(
                callback: static fn (mixed $lengthArgument): array => [
                    ['foo'],
                    ['foo'],
                    $lengthArgument,
                    null,
                ],
                array: [
                    'foo',
                    3.14,
                    0,
                    -1,
                    null,
                    false,
                    ['foo'],
                    (object)['foo'],
                    $fileHandle,
                ],
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_InvalidArguments_PreserveKeys(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return self::convertFixtures(
            fixtures: array_map(
                callback: static fn (mixed $preserveKeysArgument): array => [
                    ['foo'],
                    ['foo'],
                    null,
                    $preserveKeysArgument,
                ],
                array: [
                    'foo',
                    42,
                    3.14,
                    ['foo'],
                    (object)['foo'],
                    $fileHandle,
                ],
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
                $data[1],
                $argumentIteratorFactory->create([
                    Chunk::ARGUMENT_INDEX_LENGTH => $data[2] ?? null,
                    Chunk::ARGUMENT_INDEX_PRESERVE_KEYS => $data[3] ?? null,
                ]),
            ],
            array: $fixtures,
        );
    }
}
