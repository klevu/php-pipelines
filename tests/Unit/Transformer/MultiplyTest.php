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
use Klevu\Pipelines\Transformer\Multiply;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test context
 * @todo Test invalid constructor args
 */
#[CoversClass(Multiply::class)]
class MultiplyTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Multiply::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [0, 1, 0],
                [1, 0, 0],
                [0, 0, 0],
                [10, 5, 50],
                [4, -3, -12],
                [-4, 0, 0],
                [0, -5, 0],
                [2, -3, -6],
                [0, 2.7, 0.0],
                [6.9, 0, 0.0],
                [2, 1.5, 3.0],
                [1.5, 1.7, 2.55],
                [1.25, 1.81, 2.2625],
                [5, -2.5, -12.5],
                [-4, -4, 16],
                [-4.0, -4.0, 16.0],
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
                    Multiply::ARGUMENT_INDEX_VALUE => $data[1],
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
        $MultiplyTransformer = new Multiply();

        $this->expectException(InvalidInputDataException::class);
        $MultiplyTransformer->transform(
            data: $input,
            arguments: $arguments,
            context: $context,
        );
    }
}
