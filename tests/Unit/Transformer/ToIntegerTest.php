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
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\ToInteger;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ToInteger::class)]
class ToIntegerTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success(): array
    {
        return [
            [
                '',
                null,
                null,
                0,
            ],
            [
                ' ',
                null,
                null,
                0,
            ],
            [
                true,
                null,
                null,
                1,
            ],
            [
                141,
                null,
                null,
                141,
            ],
            [
                3.14,
                null,
                null,
                3,
            ],
            [
                '1223',
                null,
                null,
                1223,
            ],
            [
                new class () {
                    public function __toString(): string
                    {
                        return '456';
                    }
                },
                null,
                null,
                456,
            ],
            [
                new class implements \Stringable {
                    public function __toString(): string
                    {
                        return '7890';
                    }
                },
                null,
                null,
                7890,
            ],
        ];
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success')]
    public function testTransform_WithSuccess(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $toInteger = new ToInteger();
        $result = $toInteger->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame(
            $expectedResult,
            $result,
            'Expecting Result: ' . $expectedResult . ' | Actual Result: ' . $result,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData_Exception(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                ['array'],
                $argumentIteratorFactory->create([
                    ['', 'nempty'],
                ]),
                null,
            ],
            [
                new \stdClass(),
                $argumentIteratorFactory->create([
                    ['', 'nempty'],
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidData_Exception')]
    public function testTransform_WithInvalidData_Exception(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $toInteger = new ToInteger();

        $this->expectException(InvalidInputDataException::class);
        $toInteger->transform(
            data: $input,
            arguments: $arguments,
            context: $context,
        );
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                false,
                $argumentIteratorFactory->create([
                    ['', 'nEmpty'],
                ]),
                null,
            ],
            [
                null,
                $argumentIteratorFactory->create([
                    ['', 'nEmpty'],
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidData')]
    public function testTransform_WithInvalidData(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $toInteger = new ToInteger();

        $result = $toInteger->transform(
            data: $input,
            arguments: $arguments,
            context: $context,
        );
        $this->assertNotSame($input, $result);
    }
}
