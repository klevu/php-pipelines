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
use Klevu\Pipelines\Transformer\FormatNumber;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(FormatNumber::class)]
class FormatNumberTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = FormatNumber::class;

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
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [42, [], '42'],
                [-5, [], '-5'],
                [1234, [], '1,234'],
                [3.14, [], '3'],
                [3.99, [], '4'],

                [null, [2], null],
                [42, [2], '42.00'],
                [-5, [2], '-5.00'],
                [1234, [2], '1,234.00'],
                [3.14, [2], '3.14'],
                [3.14, [10], '3.1400000000'],
                [3.99, [2], '3.99'],
                [3.99, [1], '4.0'],
                [0.005, [2], '0.01'],
                [0.005, [5], '0.00500'],

                [null, [0, ':'], null],
                [42, [0, ':'], '42'],
                [-5, [0, ':'], '-5'],
                [1234, [0, ':'], '1,234'],
                [3.14, [0, ':'], '3'],
                [3.99, [0, ':'], '4'],

                [42, [2, ':'], '42:00'],
                [-5, [2, ':'], '-5:00'],
                [1234, [2, ':'], '1,234:00'],
                [3.14, [2, ':'], '3:14'],
                [3.99, [2, ':'], '3:99'],

                [null, [null, null, '-'], null],
                [42, [null, null, '-'], '42'],
                [-5, [null, null, '-'], '-5'],
                [1234, [null, null, '-'], '1-234'],
                [3.14, [null, null, '-'], '3'],
                [3.99, [null, null, '-'], '4'],

                [null, [2, ':', '-'], null],
                [42, [2, ':', '-'], '42:00'],
                [-5, [2, ':', '-'], '-5:00'],
                [1234, [2, ':', '-'], '1-234:00'],
                [3.14, [2, ':', '-'], '3:14'],
                [3.99, [2, ':', '-'], '3:99'],

                ['42.00', [2, ':', '-'], '42:00'],
                ['-5', [2, ':', '-'], '-5:00'],
                ['1234', [2, ':', '-'], '1-234:00'],
                ['3.14', [2, ':', '-'], '3:14'],
                ['3.99', [2, ':', '-'], '3:99'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [null, 42, -5, 1234, 3.14, 3.99],
                    [],
                    [null, '42', '-5', '1,234', '3', '4'],
                ],
                [
                    [null, 42, -5, 1234, 3.14, 3.99, 0.005],
                    [2],
                    [null, '42.00', '-5.00', '1,234.00', '3.14', '3.99', '0.01'],
                ],
                [
                    [null, 42, -5, 1234, 3.14, 3.99],
                    [0, ':'],
                    [null, '42', '-5', '1,234', '3', '4'],
                ],
                [
                    [42, -5, 1234, 3.14, 3.99],
                    [2, ':'],
                    ['42:00', '-5:00', '1,234:00', '3:14', '3:99'],
                ],
                [
                    [null, 42, -5, 1234, 3.14, 3.99],
                    [null, null, '-'],
                    [null, '42', '-5', '1-234', '3', '4'],
                ],
                [
                    [null, 42, -5, 1234, 3.14, 3.99],
                    [2, ':', '-'],
                    [null, '42:00', '-5:00', '1-234:00', '3:14', '3:99'],
                ],
                [
                    ['42.00', '-5', '1234', '3.14', '3.99'],
                    [2, ':', '-'],
                    ['42:00', '-5:00', '1-234:00', '3:14', '3:99'],
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
                    callback: static fn ($decimalsArgumentValue): array => [
                        42,
                        [$decimalsArgumentValue, null, null],
                        '',
                    ],
                    array: [
                        'foo',
                        3.14,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($decimalSeparatorArgumentValue): array => [
                        42,
                        [null, $decimalSeparatorArgumentValue, null],
                        '',
                    ],
                    array: [
                        42,
                        3.14,
                        true,
                        ['foo'],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($thousandsSeparatorArgumentValue): array => [
                        42,
                        [null, null, $thousandsSeparatorArgumentValue],
                        '',
                    ],
                    array: [
                        42,
                        3.14,
                        true,
                        ['foo'],
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
                        FormatNumber::ARGUMENT_INDEX_DECIMALS => $data[1][0] ?? null,
                        FormatNumber::ARGUMENT_INDEX_DECIMAL_SEPARATOR => $data[1][1] ?? null,
                        FormatNumber::ARGUMENT_INDEX_THOUSANDS_SEPARATOR => $data[1][2] ?? null,
                    ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
