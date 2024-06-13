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
use Klevu\Pipelines\Validator\IsPositiveNumber;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Add tests for Context extractions
 *
 * @method IsPositiveNumber initialiseTestObject()
 */
#[CoversClass(IsPositiveNumber::class)]
class IsPositiveNumberTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsPositiveNumber::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            self::dataProvider_testValidate_Valid_AllowZero(),
            self::dataProvider_testValidate_Valid_NotAllowZero(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            [[42]],
            [(object)[3.14]],
            [false],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        return array_merge(
            self::dataProvider_testValidate_InvalidData_AllowZero(),
            self::dataProvider_testValidate_InvalidData_NotAllowZero(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_AllowZero(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null],
                [0],
                [-0],
                [42],
                [3.14],
                [PHP_INT_MAX],
                // While PHP_INT_MIN is the smallest absolute integer (ie, <0),
                //  PHP_FLOAT_MIN is the smallest possible _positive_ floating point
                [PHP_FLOAT_MIN],
                [PHP_FLOAT_MAX],
                [1_234_567],
                [0777],
                [0o777],
                [0O777],
                [0xFFF],
                [0b010],
                ['0'],
                ['-0'],
                ['42'],
                ['3.14'],
                [(string)PHP_INT_MAX],
                [(string)PHP_FLOAT_MIN],
                [(string)PHP_FLOAT_MAX],
                ['0 '],
                [' 3.14 '],
                ['0777'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_NotAllowZero(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null],
                [42],
                [3.14],
                [PHP_INT_MAX],
                [PHP_FLOAT_MAX],
                [1_234_567],
                [0777],
                [0o777],
                [0O777],
                [0xFFF],
                [0b010],
                ['42'],
                ['3.14'],
                [(string)PHP_INT_MAX],
                [(string)PHP_FLOAT_MAX],
                [' 3.14 '],
                ['0777'],
            ],
            allowZero: false,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_AllowZero(): array
    {
        return self::convertFixtures(
            fixtures: [
                [-99],
                [-0.23],
                [PHP_INT_MIN],
                [-PHP_FLOAT_MIN],
                [-1_234_567],
                [-0777],
                [-0o777],
                [-0O777],
                [-0xFFF],
                [-0b010],
                ['-99'],
                ['-0.23'],
                [(string)PHP_INT_MIN],
                [(string)-PHP_FLOAT_MIN],
                [' -99'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_NotAllowZero(): array
    {
        return self::convertFixtures(
            fixtures: [
                [0],
                [-0],
                [-99],
                [-0.23],
                [PHP_INT_MIN],
                [-PHP_FLOAT_MIN],
                [-1_234_567],
                [-0777],
                [-0o777],
                [-0O777],
                [-0xFFF],
                [-0b010],
                ['-99'],
                ['-0.23'],
                [(string)PHP_INT_MIN],
                [(string)-PHP_FLOAT_MIN],
                [' -99'],
            ],
            allowZero: false,
        );
    }

    /**
     * @param mixed[][] $fixtures
     * @param bool|null $allowZero
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        ?bool $allowZero = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            IsPositiveNumber::ARGUMENT_INDEX_ALLOW_ZERO => $allowZero,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }
}
