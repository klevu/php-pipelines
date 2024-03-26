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
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\Add;
use Klevu\Pipelines\Transformer\Calc;
use Klevu\Pipelines\Transformer\Divide;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Divide::class)]
class DivideTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success(): array
    {
        return [
            [10, 5, 2],
            [4, -3, -1.3333333333333333],
            [2, -3, -0.6666666666666666],
            [2, 1.5, 1.3333333333333333],
            [1.5, 1.6, 0.9375],
            [1.25, 1.81, 0.6906077348066298],
            [5, -2.5, -2.0],
            [0, 2.7, 0.0],
        ];
    }

    /**
     * @param mixed $data
     * @param mixed $value
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success')]
    public function testTransform_WithSuccess(
        mixed $data,
        mixed $value,
        mixed $expectedResult,
    ): void {
        $addTransformer = new Divide();

        $arguments = new ArgumentIterator([
            new Argument(
                value: $value,
                key: Add::ARGUMENT_INDEX_VALUE,
            ),
        ]);

        $result = $addTransformer->transform(
            data: $data,
            arguments: $arguments,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData_Exception(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                false,
                $argumentIteratorFactory->create([
                    ['', 'noEmpty'],
                ]),
                null,
            ],
            [
                true,
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
            [
                [true],
                $argumentIteratorFactory->create([
                    ['', 'nEmpty'],
                ]),
                null,
            ],
            [
                ['array'],
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
            [
                new \stdClass(),
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
    #[DataProvider('dataProvider_testTransform_InvalidData_Exception')]
    public function testTransform_WithInvalidData_Exception(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $addTransformer = new Divide();

        $this->expectException(InvalidInputDataException::class);
        $this->expectExceptionMessage('Invalid input data for transformation');
        try {
            $addTransformer->transform(
                data: $input,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidInputDataException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid data\. Expected numeric\|numeric\[\], received .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: Calc::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData_CalcException(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                1.23456789,
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
    #[DataProvider('dataProvider_testTransform_InvalidData_CalcException')]
    public function testTransform_WithInvalidData_CalcException(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $addTransformer = new Divide();

        $this->expectException(InvalidTransformationArgumentsException::class);
        $this->expectExceptionMessage('Invalid argument for transformation');
        try {
            $addTransformer->transform(
                data: $input,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid Value argument \(\d+\): .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: Calc::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ZeroInvalidData_CalcException(): array
    {
        return [
            [6.9, 0, 6.9],
        ];
    }

    /**
     * @param mixed $data
     * @param mixed $value
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ZeroInvalidData_CalcException')]
    public function testTransform_WithZeroInvalidData_CalcException(
        mixed $data,
        mixed $value,
    ): void {
        $addTransformer = new Divide();
        $arguments = new ArgumentIterator([
            new Argument(
                value: $value,
                key: Add::ARGUMENT_INDEX_VALUE,
            ),
        ]);
        $this->expectException(InvalidTransformationArgumentsException::class);
        $this->expectExceptionMessage('Invalid argument for transformation');
        try {
            $addTransformer->transform(
                data: $data,
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value argument \(\d+\) must not be zero for division operations/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: Calc::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }
}
