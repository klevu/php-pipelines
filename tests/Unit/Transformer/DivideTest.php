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
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\Add;
use Klevu\Pipelines\Transformer\Calc;
use Klevu\Pipelines\Transformer\Divide;
use Klevu\Pipelines\Transformer\TransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(Divide::class)]
class DivideTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Divide::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [10, 5, 2],
                [4, -3, -1.3333333333333333],
                [2, -3, -0.6666666666666666],
                [2, 1.5, 1.3333333333333333],
                [1.5, 1.6, 0.9375],
                [1.25, 1.81, 0.6906077348066298],
                [5, -2.5, -2.0],
                [0, 2.7, 0.0],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidInputData(): array
    {
        return [
            ['foo'],
            [['foo']],
            [false],
            [[false]],
            [(object)[42]],
            [[(object)[42]]],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    1.23456789,
                    [
                        ['', 'nEmpty'],
                    ],
                    null,
                ],
            ],
        );
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[2],
                $argumentIteratorFactory->create([
                    Add::ARGUMENT_INDEX_VALUE => $data[1],
                ]),
            ],
            array: $fixtures,
        );
    }

    #[Test]
    #[TestWith([42])]
    #[TestWith([[3.14]])]
    public function testTransform_DivideByZero(
        mixed $data,
    ): void {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        /** @var TransformerInterface $transformer */
        $transformer = $this->initialiseTestObject();

        $this->expectException(InvalidTransformationArgumentsException::class);
        try {
            $transformer->transform(
                data: $data,
                arguments: $argumentIteratorFactory->create([
                    Divide::ARGUMENT_INDEX_VALUE => 0,
                ]),
            );
        } catch (TransformationException $exception) {
            $this->assertInstanceOf(
                expected: InvalidTransformationArgumentsException::class,
                actual: $exception,
            );
            $errors = $exception->getErrors();
            $this->assertNotEmpty($errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value argument \(.*\) must not be zero for division operations/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
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
}
