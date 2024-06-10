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
use Klevu\Pipelines\Transformer\Count;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(Count::class)]
class CountTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Count::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_NullOrScalar(),
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
                    ['item1', 'item2'],
                    2,
                ],
                [
                    new \ArrayIterator([5, 6, 7]),
                    3,
                ],
                [
                    [
                        new \ArrayIterator([1, 2, 3]),
                        new \ArrayIterator([1, 2, 3]),
                    ],
                    2,
                ],
                //Below is valid iterator implementation,
                //[function () { yield 1; }, function () { yield 1;}],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_NullOrScalar(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, null],
                [1, 1],
                [0, 0],
                ['', ''],
                [' ', ' '],
                ['12', '12'],
                [false, false],
                [true, true],
                [2.3, 2.3],
                [42, 42],
                ['not_iterable', 'not_iterable'],
                ['[1, 2, 3]', '[1, 2, 3]'],
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
