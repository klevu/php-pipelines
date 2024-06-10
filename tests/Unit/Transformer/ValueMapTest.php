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
use Klevu\Pipelines\Transformer\ValueMap;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test Extractions
 * @todo Test Complex lookup keys
 */
#[CoversClass(ValueMap::class)]
class ValueMapTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ValueMap::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Simple_Loose(),
            self::dataProvider_testTransform_Valid_Simple_CaseInsensitive(),
            self::dataProvider_testTransform_Valid_Recursive_Simple(),
            self::dataProvider_testTransform_Valid_Recursive_Simple_Loose(),
            self::dataProvider_testTransform_Valid_Recursive_Simple_CaseInsensitive(),
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
                    'foo',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                    ],
                    'bar',
                ],
                [
                    'baz',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                    ],
                    'baz',
                ],
                [
                    'FOO',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                    ],
                    'FOO',
                ],
                [
                    1,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                    ],
                    'one',
                ],
                [
                    '1',
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                    ],
                    '1',
                ],
                [
                    1.0,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                    ],
                    1.0,
                ],
                [
                    true,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                    ],
                    true,
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_Loose(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    'foo',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        false,
                        null,
                    ],
                    'bar',
                ],
                [
                    'baz',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        false,
                        null,
                    ],
                    'baz',
                ],
                [
                    'FOO',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        false,
                        null,
                    ],
                    'FOO',
                ],
                [
                    1,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        false,
                        null,
                    ],
                    'one',
                ],
                [
                    '1',
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        false,
                        null,
                    ],
                    'one',
                ],
                [
                    1.0,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        false,
                        null,
                    ],
                    'one',
                ],
                [
                    true,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        false,
                        null,
                    ],
                    'one',
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_CaseInsensitive(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    'foo',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        null,
                        false,
                    ],
                    'bar',
                ],
                [
                    'baz',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        null,
                        false,
                    ],
                    'baz',
                ],
                [
                    'FOO',
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        null,
                        false,
                    ],
                    'bar',
                ],
                [
                    1,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        null,
                        false,
                    ],
                    'one',
                ],
                [
                    '1',
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        null,
                        false,
                    ],
                    '1',
                ],
                [
                    1.0,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        null,
                        false,
                    ],
                    1.0,
                ],
                [
                    true,
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        null,
                        false,
                    ],
                    true,
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Recursive_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    [
                        'foo',
                        'wom',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                    ],
                    [
                        'bar',
                        'bat',
                    ],
                ],
                [
                    [
                        'FOO',
                        'bat',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                    ],
                    [
                        'FOO',
                        'bat',
                    ],
                ],
                [
                    [
                        1,
                        '1',
                        1.0,
                        true,
                    ],
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                    ],
                    [
                        'one',
                        '1',
                        1.0,
                        true,
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Recursive_Simple_Loose(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    [
                        'foo',
                        'wom',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        false,
                        null,
                    ],
                    [
                        'bar',
                        'bat',
                    ],
                ],
                [
                    [
                        'FOO',
                        'bat',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        false,
                        null,
                    ],
                    [
                        'FOO',
                        'bat',
                    ],
                ],
                [
                    [
                        1,
                        '1',
                        1.0,
                        true,
                    ],
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        false,
                        null,
                    ],
                    [
                        'one',
                        'one',
                        'one',
                        'one',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Recursive_Simple_CaseInsensitive(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    [
                        'foo',
                        'wom',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        null,
                        false,
                    ],
                    [
                        'bar',
                        'bat',
                    ],
                ],
                [
                    [
                        'FOO',
                        'bat',
                    ],
                    [
                        [
                            'foo' => 'bar',
                            'wom' => 'bat',
                        ],
                        null,
                        false,
                    ],
                    [
                        'bar',
                        'bat',
                    ],
                ],
                [
                    [
                        1,
                        '1',
                        1.0,
                        true,
                    ],
                    [
                        [
                            1 => 'one',
                            2 => 'two',
                            3 => 'three',
                        ],
                        null,
                        false,
                    ],
                    [
                        'one',
                        '1',
                        1.0,
                        true,
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

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidInputData')]
    public function testTransform_InvalidInputData(
        mixed $data,
    ): void {
        $this->markTestSkipped();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
