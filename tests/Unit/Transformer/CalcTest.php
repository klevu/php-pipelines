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
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\Test\Unit\Transformer\Traits\AddDataProviderTrait;
use Klevu\Pipelines\Test\Unit\Transformer\Traits\DivideDataProviderTrait;
use Klevu\Pipelines\Test\Unit\Transformer\Traits\MultiplyDataProviderTrait;
use Klevu\Pipelines\Test\Unit\Transformer\Traits\SubtractDataProviderTrait;
use Klevu\Pipelines\Transformer\Calc;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Calc::class)]
class CalcTest extends TestCase
{
    use AddDataProviderTrait;
    use SubtractDataProviderTrait;
    use MultiplyDataProviderTrait;
    use DivideDataProviderTrait;

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_ForAddData')]
    public function testTransform_Add_Operation_WithSuccess(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $calcTransformer = new Calc();

        $result = $calcTransformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
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
    #[DataProvider('dataProvider_testTransform_ForSubtractData')]
    public function testTransform_Subtract_Operation_WithSuccess(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $calcTransformer = new Calc();

        $result = $calcTransformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
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
    #[DataProvider('dataProvider_testTransform_ForMultiplyData')]
    public function testTransform_Multiply_Operation_WithSuccess(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $calcTransformer = new Calc();

        $result = $calcTransformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
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
    #[DataProvider('dataProvider_testTransform_ForDivideData')]
    public function testTransform_Divide_Operation_WithSuccess(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        mixed $expectedResult,
    ): void {
        $calcTransformer = new Calc();

        $result = $calcTransformer->transform(
            data: $data,
            arguments: $arguments,
            context: $context,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransformInvalid_ForAddData')]
    public function testTransform_WithInvalidInputDataException(
        mixed $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $calcTransformer = new Calc();

        $this->expectException(InvalidInputDataException::class);
        try {
            $calcTransformer->transform(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidInputDataException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid data. Expected numeric\|numeric\[\], received .*/',
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
     * @return void
     */
    #[Test]
    public function testTransform_WithInvalidOperationArgumentsException(): void
    {
        $calcTransformer = new Calc();
        $argumentIteratorFactory = new ArgumentIteratorFactory();
        $arguments = $argumentIteratorFactory->create([
            ['', 'noEmpty'],
        ]);
        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcTransformer->transform(
                data: 3.14,
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid Operation argument \(\d+\): .*/',
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
     * @return void
     */
    #[Test]
    public function testTransform_WithOperationArgumentsRequiredException(): void
    {
        $calcTransformer = new Calc();

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcTransformer->transform(
                data: 3.14,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern:'/Operation argument \(\d+\) is required/',
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
     * @return void
     */
    #[Test]
    public function testTransform_WithOperationArgumentsUnrecognisedException(): void
    {
        $calcTransformer = new Calc();
        $argumentIteratorFactory = new ArgumentIteratorFactory();
        $arguments = $argumentIteratorFactory->create([
            new Argument(
                value: '++',
                key: Calc::ARGUMENT_INDEX_OPERATION,
            ),
            new Argument(
                value: 9,
                key: Calc::ARGUMENT_INDEX_VALUE,
            ),
        ]);
        $this->expectException(
            InvalidTransformationArgumentsException::class,
        );
        try {
            $calcTransformer->transform(
                data: 3.14,
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Unrecognised Operation argument \(\d+\) value: \+\+/',
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
     * @return void
     */
    #[Test]
    public function testTransform_WithValueArguments_Empty(): void
    {
        $calcTransformer = new Calc();
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $arguments = $argumentIteratorFactory->create([
            new Argument(
                value: Operations::ADD,
                key: Calc::ARGUMENT_INDEX_OPERATION,
            ),
            new Argument(
                value: '',
                key: Calc::ARGUMENT_INDEX_VALUE,
            ),
        ]);
        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcTransformer->transform(
                data: 9,
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value argument \(\d+\) is required/',
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
     * @return void
     */
    #[Test]
    public function testTransform_WithValueArguments_Invalid(): void
    {
        $calcTransformer = new Calc();
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $arguments = $argumentIteratorFactory->create([
            new Argument(
                value: Operations::ADD,
                key: Calc::ARGUMENT_INDEX_OPERATION,
            ),
            new Argument(
                value: 'foo',
                key: Calc::ARGUMENT_INDEX_VALUE,
            ),
        ]);
        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $calcTransformer->transform(
                data: 9,
                arguments: $arguments,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value argument \(\d+\) must be numeric; received .*/',
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
