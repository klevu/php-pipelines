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
use Klevu\Pipelines\Transformer\Split;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(Split::class)]
class SplitTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Split::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Array(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [null, [], null],
                [null, [','], null],

                [
                    ' lorem ipsum sit  amet dolor ',
                    [','],
                    [' lorem ipsum sit  amet dolor '],
                ],
                [
                    ' lorem ipsum sit  amet dolor ',
                    [' '],
                    ['', 'lorem', 'ipsum', 'sit', '', 'amet', 'dolor', ''],
                ],
                [
                    'emoji😁party',
                    ['😁'],
                    ['emoji', 'party'],
                ],
            ],
        );
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [null, 'foo'],
                    [','],
                    [
                        null,
                        ['foo'],
                    ],
                ],

                [
                    [
                        ' lorem ipsum sit  amet dolor ',
                        'foo bar baz',
                    ],
                    [','],
                    [
                        [' lorem ipsum sit  amet dolor '],
                        ['foo bar baz'],
                    ],
                ],
                [
                    [
                        ' lorem ipsum sit  amet dolor ',
                        'foo bar baz',
                    ],
                    [' '],
                    [
                        ['', 'lorem', 'ipsum', 'sit', '', 'amet', 'dolor', ''],
                        ['foo', 'bar', 'baz'],
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
                    callback: static fn ($separatorArgumentValue): array => [
                        'foo',
                        [$separatorArgumentValue, null],
                        '',
                    ],
                    array: [
                        '',
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
                        Split::ARGUMENT_INDEX_SEPARATOR => $data[1][0] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
