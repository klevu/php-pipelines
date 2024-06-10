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
use Klevu\Pipelines\Model\Transformation\StringPositions;
use Klevu\Pipelines\Transformer\Trim;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(Trim::class)]
class TrimTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Trim::class;

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

                ['foo', [], 'foo'],
                [' bar', [], 'bar'],
                ['baz ', [], 'baz'],
                [' wom bat ', [], 'wom bat'],

                [' %foo %', ['%'], ' %foo '],
                [' %foo %', [' '], '%foo %'],
                [' %foo %', ['% '], 'foo'],
                [' %foo %', [' %'], 'foo'],

                ['foo', [null, StringPositions::BOTH], 'foo'],
                [' bar', [null, StringPositions::BOTH], 'bar'],
                ['baz ', [null, StringPositions::BOTH], 'baz'],
                [' wom bat ', [null, StringPositions::BOTH], 'wom bat'],

                ['foo', [null, 'both'], 'foo'],
                [' bar', [null, 'both'], 'bar'],
                ['baz ', [null, 'both'], 'baz'],
                [' wom bat ', [null, 'both'], 'wom bat'],

                ['foo', [null, StringPositions::START], 'foo'],
                [' bar', [null, StringPositions::START], 'bar'],
                ['baz ', [null, StringPositions::START], 'baz '],
                [' wom bat ', [null, StringPositions::START], 'wom bat '],

                ['foo', [null, 'start'], 'foo'],
                [' bar', [null, 'start'], 'bar'],
                ['baz ', [null, 'start'], 'baz '],
                [' wom bat ', [null, 'start'], 'wom bat '],

                ['foo', [null, StringPositions::END], 'foo'],
                [' bar', [null, StringPositions::END], ' bar'],
                ['baz ', [null, StringPositions::END], 'baz'],
                [' wom bat ', [null, StringPositions::END], ' wom bat'],

                ['foo', [null, 'end'], 'foo'],
                [' bar', [null, 'end'], ' bar'],
                ['baz ', [null, 'end'], 'baz'],
                [' wom bat ', [null, 'end'], ' wom bat'],

                [' %foo %', ['%', 'both'], ' %foo '],
                [' %foo %', ['%', 'start'], ' %foo %'],
                [' %foo %', ['%', 'end'], ' %foo '],
                [' %foo %', [' ', 'both'], '%foo %'],
                [' %foo %', [' ', 'start'], '%foo %'],
                [' %foo %', [' ', 'end'], ' %foo %'],
                [' %foo %', ['% ', 'both'], 'foo'],
                [' %foo %', ['% ', 'start'], 'foo %'],
                [' %foo %', ['% ', 'end'], ' %foo'],
                [' %foo %', [' %', 'both'], 'foo'],
                [' %foo %', [' %', 'start'], 'foo %'],
                [' %foo %', [' %', 'end'], ' %foo'],
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
                    [null],
                    [],
                    [null],
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
                    callback: static fn ($charactersArgumentValue): array => [
                        'foo',
                        [$charactersArgumentValue, null],
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
                array_map(
                    callback: static fn ($positionArgumentValue): array => [
                        'foo',
                        [null, $positionArgumentValue],
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
                    Trim::ARGUMENT_INDEX_CHARACTERS => $data[1][0] ?? null,
                    Trim::ARGUMENT_INDEX_POSITION => $data[1][1] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
