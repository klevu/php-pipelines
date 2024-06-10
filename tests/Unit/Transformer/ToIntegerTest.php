<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Transformer\ToInteger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(ToInteger::class)]
class ToIntegerTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ToInteger::class;

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
                [null, 0],
                ['', 0],
                [' ', 0],
                [true, 1],
                [false, 0],
                [3.14, 3],
                ['3.14', 3],
                [3.99, 3],
                ['42', 42],
                [0b111, 7],
                [0o777, 511],
                [0xFFF, 4095],
                [1_234, 1234],
                [
                    new class () {
                        public function __toString(): string
                        {
                            return '456.50';
                        }
                    },
                    456,
                ],
                [
                    new class () implements \Stringable {
                        public function __toString(): string
                        {
                            return '7890';
                        }
                    },
                    7890,
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
                    [null, '', ' '],
                    [0, 0, 0],
                ],
                [
                    [true, false],
                    [1, 0],
                ],
                [
                    [3.14, '3.14', 3.99, '42'],
                    [3, 3, 3, 42],
                ],
                [
                    [0b111, 0o777, 0xFFF, 1_234],
                    [7, 511, 4095, 1234],
                ],
                [
                    [
                        new class () {
                            public function __toString(): string
                            {
                                return '456.50';
                            }
                        },
                        new class () implements \Stringable {
                            public function __toString(): string
                            {
                                return '7890';
                            }
                        },
                    ],
                    [
                        456,
                        7890,
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
        return [];
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[1],
            ],
            array: $fixtures,
        );
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    /**
     * @param mixed $data
     * @param mixed|null $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[TestWith([null])]
    public function testTransform_InvalidArguments(
        mixed $data,
        mixed $expectedResult = null,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        $this->markTestSkipped();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
