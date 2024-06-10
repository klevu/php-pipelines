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
use Klevu\Pipelines\Transformer\Add;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test context
 * @todo Test invalid constructor args
 */
#[CoversClass(Add::class)]
class AddTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = Add::class;

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [0, 1, 1],
                [1, 0, 1],
                [0, 0, 0],
                [10, 5, 15],
                [4, -3, 1],
                [-4, 0, -4],
                [0, -5, -5],
                [2, -3, -1],
                [0, 2.7, 2.7],
                [6.9, 0, 6.9],
                [2, 1.5, 3.5],
                [1.5, 1.6, 3.1],
                [1.25, 1.81, 3.06],
                [5, -2.5, 2.5],
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
        $addTransformer = new Add();

        $this->expectException(InvalidInputDataException::class);
        $addTransformer->transform(
            data: $input,
            arguments: $arguments,
            context: $context,
        );
    }
}
