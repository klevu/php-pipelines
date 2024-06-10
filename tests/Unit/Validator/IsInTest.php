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
use Klevu\Pipelines\Validator\IsIn;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @todo Add tests for Context extractions
 * @todo Add tests for Invalid Arguments
 *
 * @method IsIn initialiseTestObject()
 */
#[CoversClass(IsIn::class)]
class IsInTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsIn::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {

        return array_merge(
            [
                [null],
            ],
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
        return [];
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
                    'bar',
                    ['foo', 'bar', 'baz'],
                ],
                [
                    1,
                    [1, 2, 3],
                ],
                [
                    true,
                    [1, 2, 3],
                ],
                [
                    false,
                    [0, 1, 2, 3],
                ],
                [
                    null,
                    [0, 1, 2, 3],
                ],
                [
                    null,
                    [0, 1, 2, 3, null],
                ],
                [
                    '1',
                    [1, 2, 3],
                ],
                [
                    '1',
                    ['a' => 1, 'b' => 2, 'c' => 3],
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
                    'bar',
                    ['foo', 'bar', 'baz'],
                ],
                [
                    1,
                    [1, 2, 3],
                ],
                [
                    null,
                    [0, 1, 2, 3, null],
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
                    'bar',
                    new TestIterator(['foo', 'bar', 'baz']),
                ],
                [
                    1,
                    new TestIterator([1, 2, 3]),
                ],
                [
                    true,
                    new TestIterator([1, 2, 3]),
                ],
                [
                    false,
                    new TestIterator([0, 1, 2, 3]),
                ],
                [
                    null,
                    new TestIterator([0, 1, 2, 3]),
                ],
                [
                    '1',
                    new TestIterator([1, 2, 3]),
                ],
                [
                    '1',
                    new TestIterator(['a' => 1, 'b' => 2, 'c' => 3]),
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
                    'Bar',
                    ['foo', 'bar', 'baz'],
                ],
                [
                    ' bar',
                    ['foo', 'bar', 'baz'],
                ],
                [
                    'bar ',
                    ['foo', 'bar', 'baz'],
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
                    true,
                    [1, 2, 3],
                ],
                [
                    false,
                    [0, 1, 2, 3],
                ],
                [
                    '1',
                    [1, 2, 3],
                ],
                [
                    '1',
                    ['a' => 1, 'b' => 2, 'c' => 3],
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
                            IsIn::ARGUMENT_INDEX_HAYSTACK => $data[1],
                            IsIn::ARGUMENT_INDEX_STRICT => $strict,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }

    #[Test]
    #[TestWith([null])]
    public function testValidate_InvalidType(
        mixed $data, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        $this->markTestSkipped();
    }
}
