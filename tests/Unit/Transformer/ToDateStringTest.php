<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\ToDateString;
use Klevu\Pipelines\Transformer\TransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(ToDateString::class)]
class ToDateStringTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ToDateString::class;

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

                ['2024-01-01T00:00:00+00:00', [], '2024-01-01T00:00:00+00:00'],
                ['2024-01-01 13:00:00', [], '2024-01-01T13:00:00+00:00'],
                ['12/31/2024', [], '2024-12-31T00:00:00+00:00'],
                ['31-12-2024', [], '2024-12-31T00:00:00+00:00'],
                [1704067200, [], '2024-01-01T00:00:00+00:00'],

                ['2024-01-01 13:00:00', ['c'], '2024-01-01T13:00:00+00:00'],
                ['2024-01-01 13:00:00', ['Y-m-d H:i:s'], '2024-01-01 13:00:00'],
                ['2024-01-01 13:00:00', ['D, jS F Y \a\t g:ia'], 'Mon, 1st January 2024 at 1:00pm'],

                [
                    '2024-01-01 13:00:00',
                    ['c', 'Asia/Ulaanbaatar'],
                    '2024-01-01T21:00:00+08:00',
                ],
                [
                    '2024-01-01 13:00:00',
                    ['Y-m-d H:i:s', 'America/North_Dakota/New_Salem'],
                    '2024-01-01 07:00:00',
                ],
                [
                    '2024-01-01 13:00:00',
                    ['D, jS F Y \a\t g:ia', 'Pacific/Tongatapu'],
                    'Tue, 2nd January 2024 at 2:00am',
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
                    [null],
                    [],
                    [null],
                ],
                [
                    [
                        '2024-01-01T00:00:00+00:00',
                        '2024-01-01 13:00:00',
                        '12/31/2024',
                        '31-12-2024',
                        1704067200,
                    ],
                    [],
                    [
                        '2024-01-01T00:00:00+00:00',
                        '2024-01-01T13:00:00+00:00',
                        '2024-12-31T00:00:00+00:00',
                        '2024-12-31T00:00:00+00:00',
                        '2024-01-01T00:00:00+00:00',
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
            [3.14],
            [false],
            [(object)['foo']],
            [$fileHandle],

            ['0'],
            ['foo'],
            ['10000-01-01 00:00:00'],
            ['12-31-2024'],
            ['31/12/2024'],
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
                    callback: static fn ($formatArgumentValue): array => [
                        time(),
                        [$formatArgumentValue, null],
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
                    callback: static fn ($toTimezoneArgumentValue): array => [
                        time(),
                        [null, $toTimezoneArgumentValue],
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
                    ToDateString::ARGUMENT_INDEX_FORMAT => $data[1][0] ?? null,
                    ToDateString::ARGUMENT_INDEX_TO_TIMEZONE => $data[1][1] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }

    #[Test]
    #[TestWith(['America/Los Angeles'])]
    public function testTransform_InvalidTimezone(
        mixed $timezone,
    ): void {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidInputDataException::class);
        $transformer->transform(
            data: '2024-01-01 00:00:00',
            arguments: $argumentIteratorFactory->create([
                'c',
                $timezone,
            ]),
        );
    }
}
