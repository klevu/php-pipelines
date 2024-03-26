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
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToFloat::class)]
class ToFloatTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success(): array
    {
        return [
            [
                3.14,
                null,
                null,
                3.14,
            ],
        ];
    }

    /**
     * @param mixed $sourceValue
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success')]
    public function testTransform_WithSuccess(
        mixed $sourceValue,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $toFloat = new ToFloat();
        $result = $toFloat->transform(
            data: $sourceValue,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
    }
}
