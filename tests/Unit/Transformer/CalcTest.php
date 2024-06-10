<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\Transformer\Calc;
use Klevu\Pipelines\Transformer\TransformerInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @todo Test context
 * @todo Test invalid constructor args
 */
#[CoversClass(Calc::class)]

class CalcTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Calc::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: array_merge(
                self::dataProvider_testTransform_Valid_Add(),
                self::dataProvider_testTransform_Valid_Divide(),
                self::dataProvider_testTransform_Valid_Multiply(),
                self::dataProvider_testTransform_Valid_Subtract(),
                self::dataProvider_testTransform_Valid_Pow(),
                self::dataProvider_testTransform_Valid_Recursive(),
            ),
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
    public static function dataProvider_testTransform_Valid_Add(): array
    {
        return [
            [null, Operations::ADD, 2, null],
            [0, Operations::ADD, 0, 0],
            [7, Operations::ADD, 2, 9],
            [0, Operations::ADD, 2, 2],
            [4.5, Operations::ADD, 0.5, 5.0],
            [5, Operations::ADD, 3.9, 8.9],
            [1.25, Operations::ADD, 1.81, 3.06],
            ['0', Operations::ADD, '0', 0],
            ['1.0', Operations::ADD, '0.0', 1.0],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Divide(): array
    {
        return [
            [null, Operations::DIVIDE, 1, null],
            // No divide by zero
            [1, Operations::DIVIDE, 1, 1],
            [4, Operations::DIVIDE, 2, 2],
            [4.5, Operations::DIVIDE, 0.5, 9.0],
            [5, Operations::DIVIDE, 3.9, 1.2820512820512822],
            [5.67, Operations::DIVIDE, 2.34, 2.4230769230769234],
            ['1', Operations::DIVIDE, '1', 1],
            ['1.0', Operations::DIVIDE, '1.0', 1.0],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Multiply(): array
    {
        return [
            [null, Operations::MULTIPLY, 1, null],
            [0, Operations::MULTIPLY, 0, 0],
            [7, Operations::MULTIPLY, 2, 14],
            [0, Operations::MULTIPLY, 2, 0],
            [4.5, Operations::MULTIPLY, 0.5, 2.25],
            [5, Operations::MULTIPLY, 3.9, 19.5],
            [1.25, Operations::MULTIPLY, 1.81, 2.2625],
            ['2', Operations::MULTIPLY, '2', 4],
            ['2.0', Operations::MULTIPLY, '2.0', 4.0],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Subtract(): array
    {
        return [
            [null, Operations::SUBTRACT, 1, null],
            [0, Operations::SUBTRACT, 0, 0],
            [7, Operations::SUBTRACT, 2, 5],
            [0, Operations::SUBTRACT, 2, -2],
            [4.5, Operations::SUBTRACT, 0.5, 4.0],
            [5, Operations::SUBTRACT, 3.9, 1.1],
            [1.25, Operations::SUBTRACT, 1.81, -0.56],
            ['1.0', Operations::SUBTRACT, '1.0', 0.0],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid_Pow(): array
    {
        return [
            [null, Operations::POW, 1, null],
            [7, Operations::POW, 2, 49.0],
            [0, Operations::POW, 2, 0.0],
            [4.5, Operations::POW, 0.5, 2.1213203435596424],
            [5, Operations::POW, 3.9, 532.0874515754903],
            [1.25, Operations::POW, 1.81, 1.4976389398098011],
            ['1.0', Operations::POW, '1.0', 1.0],
        ];
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Recursive(): array
    {
        return [
            [
                [null, null],
                Operations::ADD,
                1,
                [null, null],
            ],
            [
                [1, 3.15, -50],
                Operations::ADD,
                2,
                [3, 5.15, -48],
            ],
            [
                ['1', '3.15', '-50'],
                Operations::ADD,
                2,
                [3, 5.15, -48],
            ],
            [
                [1, 3.15, -50],
                Operations::ADD,
                '2',
                [3, 5.15, -48],
            ],
            [
                [null, null],
                Operations::SUBTRACT,
                1,
                [null, null],
            ],
            [
                [1, 3.15, -50],
                Operations::SUBTRACT,
                2,
                [-1, 1.15, -52],
            ],
            [
                ['1', '3.15', '-50'],
                Operations::SUBTRACT,
                2,
                [-1, 1.15, -52],
            ],
            [
                [1, 3.15, -50],
                Operations::SUBTRACT,
                '2',
                [-1, 1.15, -52],
            ],
            [
                [null, null],
                Operations::MULTIPLY,
                1,
                [null, null],
            ],
            [
                [1, 3.15, -50],
                Operations::MULTIPLY,
                2,
                [2, 6.3, -100],
            ],
            [
                ['1', '3.15', '-50'],
                Operations::MULTIPLY,
                2,
                [2, 6.3, -100],
            ],
            [
                [1, 3.15, -50],
                Operations::MULTIPLY,
                '2',
                [2, 6.3, -100],
            ],
            [
                [null, null],
                Operations::DIVIDE,
                1,
                [null, null],
            ],
            [
                [1, 3.15, -50],
                Operations::DIVIDE,
                2,
                [0.5, 1.575, -25],
            ],
            [
                [1, 3.15, -50],
                Operations::DIVIDE,
                2,
                [0.5, 1.575, -25],
            ],
            [
                ['1', '3.15', '-50'],
                Operations::DIVIDE,
                2,
                [0.5, 1.575, -25],
            ],
            [
                [1, 3.15, -50],
                Operations::DIVIDE,
                '2',
                [0.5, 1.575, -25],
            ],
            [
                [null, null],
                Operations::DIVIDE,
                1,
                [null, null],
            ],
            [
                [1, 3.15, -50],
                Operations::POW,
                2,
                [1.0, 9.9225, 2500.0],
            ],
            [
                ['1', '3.15', '-50'],
                Operations::POW,
                2,
                [1.0, 9.9225, 2500.0],
            ],
            [
                [1, 3.15, -50],
                Operations::POW,
                '2',
                [1.0, 9.9225, 2500.0],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return self::convertFixtures(
            fixtures: [
                // Invalid operation
                [3.14, null, 10, ''],
                [3.14, false, 10, ''],
                [3.14, '', 10, ''],
                [3.14, 42, 10, ''],
                [3.14, 3.14, 10, ''],
                [3.14, [], 10, ''],
                [3.14, (object)[], 10, ''],
                [3.14, $fileHandle, 10, ''],
                // Invalid value
                [3.14, Operations::ADD, null, ''],
                [3.14, Operations::ADD, false, ''],
                [3.14, Operations::ADD, '', ''],
                [3.14, Operations::ADD, [], ''],
                [3.14, Operations::ADD, (object)[], ''],
                [3.14, Operations::ADD, $fileHandle, ''],
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
                $data[3],
                $argumentIteratorFactory->create([
                    Calc::ARGUMENT_INDEX_OPERATION => $data[1],
                    Calc::ARGUMENT_INDEX_VALUE => $data[2],
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
                    Calc::ARGUMENT_INDEX_OPERATION => Operations::DIVIDE,
                    Calc::ARGUMENT_INDEX_VALUE => 0,
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
}
