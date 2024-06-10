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
use Klevu\Pipelines\Transformer\Min;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(Min::class)]
class MinTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Min::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
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
