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
use Klevu\Pipelines\Transformer\Count;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Count::class)]
class CountTest extends TestCase
{
    /**
     * Data provider for testTransform
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForCountData(): array
    {
        return [
            [1, 1],
            [0, 0],
            ['', ''],
            [' ', ' '],
            ['12', '12'],
            [false, false],
            [true, true],
            [null, null],
            [2.3, 2.3],
            [42, 42],
            ['not_iterable', 'not_iterable'],
            [['item1', 'item2'], 2],
            [new \ArrayIterator([5, 6, 7]), 3],
            [[new \ArrayIterator([1, 2, 3]), new \ArrayIterator([1, 2, 3])], 2],
            //Below is valid iterator implementation,
            //[function () { yield 1; }, function () { yield 1;}],
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
        $countTransformer = new Count();

        $result = $countTransformer->transform($input);

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
    public function testTransformWithInvalidIterableData(mixed $input): void
    {
        $countTransformer = new Count();
        $this->expectException(InvalidInputDataException::class);
        $countTransformer->transform(
            $input,
        );
    }
}
