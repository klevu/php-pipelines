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
use Klevu\Pipelines\Transformer\FirstItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FirstItem::class)]
class FirstItemTest extends TestCase
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
            [['hemoglobin', 'vitamin'], 'hemoglobin'],
            [new \ArrayIterator([5, 6, 7]), 5],
            [new \ArrayIterator([false, true, '']), false],
            [new \ArrayIterator(['', true, '']), ''],
            //Below is valid iterator implementation, but complains it is closure
            //[function () { yield 2; }, function () { yield 2;}],
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
                99,
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
        $firstItemTransformer = new FirstItem();

        $result = $firstItemTransformer->transform($input);

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
        $firstItemTransformer = new FirstItem();
        $this->expectException(InvalidInputDataException::class);
        $firstItemTransformer->transform(
            $input,
        );
    }

    /**
     * @return void
     */
    #[Test]
    public function testTransform_WithObjectToArrayMethod(): void
    {
        $this->markTestSkipped('This test');

//        $firstItemTransformer = new FirstItem();
//        $object = new class {
//            public function toArray(): array
//            {
//                return [1, 2, 3];
//            }
//        };
//
//        $result = $firstItemTransformer->transform(
//            $object,
//        );
//        $this->assertSame([1, 2, 3], $result);
    }
}
