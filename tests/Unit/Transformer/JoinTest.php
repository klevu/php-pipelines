<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\Join;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test with constructor args
 * @todo Test extractions
 */
#[CoversClass(Join::class)]
class JoinTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Join::class;

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
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return self::convertFixtures(
            fixtures: [
                [null, [], null],
                [null, [','], null],

                [
                    ['foo'],
                    [],
                    'foo',
                ],
                [
                    ['foo'],
                    ['bar'],
                    'foo',
                ],
                [
                    [1, 2, 3],
                    [],
                    '123',
                ],
                [
                    [1, 2, 3],
                    ['.'],
                    '1.2.3',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [],
                    'foobarbaz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [null],
                    'foobarbaz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [''],
                    'foobarbaz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [false],
                    'foobarbaz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [true],
                    'foo1bar11baz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    ['  =  '],
                    'foo  =  bar  =    =  baz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [42],
                    'foo42bar4242baz',
                ],
                [
                    ['foo', 'bar', '', 'baz'],
                    [3.14],
                    'foo3.14bar3.143.14baz',
                ],
                [
                    $argumentIteratorFactory->create(
                        ['foo', 'bar', '', 'baz'],
                    ),
                    [3.14],
                    'foo3.14bar3.143.14baz',
                ],
                [
                    [
                        new Argument('foo'),
                        new Argument('bar'),
                        new Argument(''),
                        new Argument('baz'),
                    ],
                    [3.14],
                    'foo3.14bar3.143.14baz',
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
            [['foo' => ['bar' => 'baz']]],
            [[(object)['foo']]],
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
                        ['foo'],
                        [$separatorArgumentValue, null],
                        '',
                    ],
                    array: [
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
                    Join::ARGUMENT_INDEX_SEPARATOR => $data[1][0] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
