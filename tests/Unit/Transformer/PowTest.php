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
use Klevu\Pipelines\Transformer\Pow;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test context
 * @todo Test invalid constructor args
 */
#[CoversClass(Pow::class)]
class PowTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Pow::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [0, 1, 0.0],
                [1, 0, 1.0],
                [0, 0, 1.0],
                [10, 5, 100000.0],
                [4, -3, 0.015625],
                [-4, 0, 1.0],
                [0, -5, INF],
                [2, -3, 0.125],
                [0, 2.7, 0.0],
                [6.9, 0, 1.0],
                [2, 1.5, 2.8284271247461903],
                [1.5, 1.7, 1.9923018599150013],
                [1.25, 1.81, 1.4976389398098011],
                [5, -2.5, 0.01788854381999832],
                [-4, -4, 0.00390625],
                [-4.0, -4.0, 0.00390625],
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
                    Pow::ARGUMENT_INDEX_EXPONENT => $data[1],
                ]),
            ],
            array: $fixtures,
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidData_Exception(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    false,
                    [
                        ['', 'noEmpty'],
                    ],
                    null,
                ],
                [
                    true,
                    [
                        ['', 'notEmpty'],
                    ],
                    null,
                ],
                [
                    [true],
                    [
                        ['', 'nEmpty'],
                    ],
                    null,
                ],
                [
                    ['array'],
                    [
                        ['', 'notEmpty'],
                    ],
                    null,
                ],
                [
                    new \stdClass(),
                    [
                        ['', 'nEmpty'],
                    ],
                    null,
                ],
            ],
        );
    }

    /**
     * @param mixed $input
     * @param mixed $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidData_Exception')]
    public function testTransform_WithInvalidData_Exception(
        mixed $input,
        mixed $expectedResult, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $PowTransformer = new Pow();

        $this->expectException(InvalidInputDataException::class);
        $PowTransformer->transform(
            data: $input,
            arguments: $arguments,
            context: $context,
        );
    }
}
