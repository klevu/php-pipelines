<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Validator\Contains;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Add tests for Context extractions
 *
 * @method Contains initialiseTestObject()
 */
#[CoversClass(Contains::class)]
class ContainsTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = Contains::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            self::dataProvider_testValidate_Valid_Array(),
            self::dataProvider_testValidate_Valid_Array_Strict(),
            self::dataProvider_testValidate_Valid_Iterator(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            ['foo'],
            [42],
            [3.14],
            [(object)['foo']],
            [false],
            [null],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        return array_merge(
            self::dataProvider_testValidate_InvalidData_Array(),
            self::dataProvider_testValidate_InvalidData_Array_Strict(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['foo', 'bar', 'baz'],
                    'bar',
                ],
                [
                    [1, 2, 3],
                    1,
                ],
                [
                    [1, 2, 3],
                    true,
                ],
                [
                    [0, 1, 2, 3],
                    false,
                ],
                [
                    [0, 1, 2, 3],
                    null,
                ],
                [
                    [0, 1, 2, 3, null],
                    null,
                ],
                [
                    [1, 2, 3],
                    '1',
                ],
                [
                    ['a' => 1, 'b' => 2, 'c' => 3],
                    '1',
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Array_Strict(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['foo', 'bar', 'baz'],
                    'bar',
                ],
                [
                    [1, 2, 3],
                    1,
                ],
            ],
            strict: true,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Iterator(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    new TestIterator(['foo', 'bar', 'baz']),
                    'bar',
                ],
                [
                    new TestIterator([1, 2, 3]),
                    1,
                ],
                [
                    new TestIterator([1, 2, 3]),
                    true,
                ],
                [
                    new TestIterator([0, 1, 2, 3]),
                    false,
                ],
                [
                    new TestIterator([0, 1, 2, 3]),
                    null,
                ],
                [
                    new TestIterator([1, 2, 3]),
                    '1',
                ],
                [
                    new TestIterator(['a' => 1, 'b' => 2, 'c' => 3]),
                    '1',
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    ['foo', 'bar', 'baz'],
                    'Bar',
                ],
                [
                    ['foo', 'bar', 'baz'],
                    ' bar',
                ],
                [
                    ['foo', 'bar', 'baz'],
                    'bar ',
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Array_Strict(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [1, 2, 3],
                    true,
                ],
                [
                    [0, 1, 2, 3],
                    false,
                ],
                [
                    [0, 1, 2, 3],
                    null,
                ],
                [
                    [1, 2, 3],
                    '1',
                ],
                [
                    ['a' => 1, 'b' => 2, 'c' => 3],
                    '1',
                ],
            ],
            strict: true,
        );
    }

    /**
     * @param mixed[][] $fixtures
     * @param bool|null $strict
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        ?bool $strict = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            Contains::ARGUMENT_INDEX_NEEDLE => $data[1],
                            Contains::ARGUMENT_INDEX_STRICT => $strict,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }
}
