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
use Klevu\Pipelines\Test\Fixture\TestArrayAccess;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Transformer\Keys;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(Keys::class)]
class KeysTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Keys::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return [
            [
                null,
                null,
            ],
            [
                [1, 2, 3],
                [0, 1, 2],
            ],
            [
                [
                    'foo' => 'bar',
                    'baz',
                    'wom' => 'bat',
                ],
                ['foo', 0, 'wom'],
            ],
            [
                new TestIterator([1, 2, 3]),
                [0, 1, 2],
            ],
            [
                new TestIterator([
                    'foo' => 'bar',
                    'baz',
                    'wom' => 'bat',
                ]),
                [0, 1, 2],
            ],
            [
                new TestArrayAccess([1, 2, 3]),
                [0, 1, 2],
            ],
            [
                new TestArrayAccess([
                    'foo' => 'bar',
                    'baz',
                    'wom' => 'bat',
                ]),
                ['foo', 0, 'wom'],
            ],
        ];
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
            ['foo'],
            [42],
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
        return [];
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
