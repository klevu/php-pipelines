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
use Klevu\Pipelines\Transformer\Min;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Min::class)]
class MinTest extends TestCase
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
            [['hemoglobin', 'vitamin','globin','d'], 'd'],
            [new \ArrayIterator([5, 6, 3]), 3],
            [new \ArrayIterator([false, true, '']), false],
            [new \ArrayIterator([true, true, '']), ''],
            //[function () { yield 2; }, function () { yield 2;}],
            [[0, 0.00000001], 0],
            [[0.12, 0.00000001], 1.0E-8],
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
                -17,
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
        $maxTransformer = new Min();

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
        $maxTransformer = new Min();
        $this->expectException(InvalidInputDataException::class);
        $maxTransformer->transform(
            $input,
        );
    }
}
