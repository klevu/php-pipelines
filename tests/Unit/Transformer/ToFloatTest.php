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
use Klevu\Pipelines\Transformer\ToFloat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(ToFloat::class)]
class ToFloatTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ToFloat::class;

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
                [null, 0.0],
                ['', 0.0],
                [' ', 0.0],
                [true, 1.0],
                [false, 0.0],
                [3.14, 3.14],
                [3, 3.0],
                ['3.14', 3.14],
                ['3', 3.0],
                [0b111, 7.0],
                [0o777, 511.0],
                [0xFFF, 4095.0],
                [1_234, 1234.0],
                [
                    new class () {
                        public function __toString(): string
                        {
                            return '456';
                        }
                    },
                    456.0,
                ],
                [
                    new class () implements \Stringable {
                        public function __toString(): string
                        {
                            return '7890';
                        }
                    },
                    7890.0,
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
                    [0.0, 0.0, 0.0],
                ],
                [
                    [true, false],
                    [1.0, 0.0],
                ],
                [
                    [3.14, '3.14'],
                    [3.14, 3.14],
                ],
                [
                    [3, '3'],
                    [3.0, 3.0],
                ],
                [
                    [0b111, 0o777, 0xFFF, 1_234],
                    [7.0, 511.0, 4095.0, 1234.0],
                ],
                [
                    [
                        new class () {
                            public function __toString(): string
                            {
                                return '456';
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
                        456.0,
                        7890.0,
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
