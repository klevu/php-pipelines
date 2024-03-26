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
use Klevu\Pipelines\Transformer\Max;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Max::class)]
class MaxTest extends TestCase
{
    /**
     * Data provider for testTransform
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForCountData(): array
    {
        return [
            [null, null],
            [3.14, 3.14],
            [true, true],
            [false, false],
            [0, 0],
            ['0', '0'],
            ['', ''],
            ["", ""],
            [' ', ' '],
            [" ", " "],
            [[-18], -18],
            [[-19.9], -19.9],
            [['hemoglobin', 'vitamin'], 'vitamin'],
            [new \ArrayIterator([5, 6, 7]), 7],
            [new \ArrayIterator([false, true, '']), true],
            [new \ArrayIterator([true, true, '']), true],
            //[function () { yield 2; }, function () { yield 2;}],
            [[0, 0.00000001], 1.0E-8],
            [[0.12, 0.00000001], 0.12],
            [
                [
                    99,
                    42,
                    3.14,
                    -17,
                    10025,
                    9.99,
                    1,
                ],
                10025,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ForCountData')]
    public function testTransform_Count_Operation_WithSuccess(
        mixed $input,
        mixed $expectedResult,
    ): void {
        $maxTransformer = new Max();

        $result = $maxTransformer->transform($input);

        $this->assertEquals($expectedResult, $result);
    }

    /**
     * Data provider for testTransform
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForCountData_Invalid(): array
    {
        return [
            [new \stdClass()],
        ];
    }

    /**
     * @param mixed $input
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ForCountData_Invalid')]
    public function testTransform_WithInvalidIterableData(mixed $input): void
    {
        $maxTransformer = new Max();
        $this->expectException(InvalidInputDataException::class);
        $maxTransformer->transform(
            $input,
        );
    }
}
