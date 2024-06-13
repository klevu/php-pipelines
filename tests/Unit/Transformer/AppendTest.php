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
use Klevu\Pipelines\Transformer\AbstractConcatenate;
use Klevu\Pipelines\Transformer\Append;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test Extractions
 */
#[CoversClass(Append::class)]
#[CoversClass(AbstractConcatenate::class)]
class AppendTest extends AbstractTransformerTestCase
{
    protected string $transformerFqcn = Append::class;

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
                [
                    null,
                    ['bar'],
                    null,
                ],
                [
                    'foo',
                    ['bar'],
                    'foobar',
                ],
                [
                    1,
                    [2],
                    '12',
                ],
                [
                    3.14,
                    [42],
                    '3.1442',
                ],
                [
                    true,
                    [false, null],
                    '1',
                ],
                [
                    'foo',
                    ['bar', 'baz'],
                    'foobarbaz',
                ],
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
                    [null, null],
                    ['bar'],
                    [null, null],
                ],
                [
                    ['foo', 'bar'],
                    ['bar'],
                    ['foobar', 'barbar'],
                ],
                [
                    [1, 0],
                    [2],
                    ['12', '02'],
                ],
                [
                    [3.14, -1.23],
                    [42],
                    ['3.1442', '-1.2342'],
                ],
                [
                    [true, false],
                    [false, null],
                    ['1', ''],
                ],
                [
                    ['foo', 'bar'],
                    ['bar', 'baz'],
                    ['foobarbaz', 'barbarbaz'],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidInputData(): array
    {
        return [
            [(object)[42]],
            [[(object)[42]]],
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
            fixtures: [
                [
                    'foo',
                    [['bar']],
                    'bar',
                ],
                [
                    'foo',
                    [(object)['bar']],
                    'bar',
                ],
                [
                    'foo',
                    [$fileHandle],
                    'bar',
                ],
            ],
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
                $data[2],
                // @phpstan-ignore-next-line We know this is an array
                $argumentIteratorFactory->create($data[1]),
            ],
            array: $fixtures,
        );
    }
}
