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
use Klevu\Pipelines\Transformer\Merge;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test with constructor args
 * @todo Test extractions
 */
#[CoversClass(Merge::class)]
class MergeTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Merge::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
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
                [null, [','], null],

                [
                    ['foo'],
                    [
                        ['bar', 'baz'],
                    ],
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ['foo'],
                    [
                        ['bar'],
                        ['baz'],
                    ],
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ['foo' => 'foo'],
                    [
                        ['foo' => 'bar'],
                        ['foo' => 'baz'],
                    ],
                    ['foo' => 'baz'],
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
                    callback: static fn (mixed $args): array => [
                        ['foo'],
                        $args,
                        '',
                    ],
                    array: [
                        null,
                        [null],
                        ['foo'],
                        [42],
                        [3.14],
                        [false],
                        [(object)['foo']],
                        [$fileHandle],
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
                    ? $argumentIteratorFactory->create($data[1])
                    : $data[1] ?? null,
            ],
            array: $fixtures,
        );
    }
}
