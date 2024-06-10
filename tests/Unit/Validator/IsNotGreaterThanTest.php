<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Validator\IsNotGreaterThan;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Add tests for Context extractions
 *
 * @method IsNotGreaterThan initialiseTestObject()
 */
#[CoversClass(IsNotGreaterThan::class)]
class IsNotGreaterThanTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsNotGreaterThan::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            [
                [null],
            ],
            self::dataProvider_testValidate_Valid_Simple(),
            self::dataProvider_testValidate_Valid_Equal(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            [false],
            [[42]],
            [(object)[3.14]],
            ['foo'],
            ['123a'],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        return array_merge(
            self::dataProvider_testValidate_InvalidData_Simple(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Simple(): array
    {
        return self::convertFixtures(
            fixtures: self::getFixtures(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: array_map('array_reverse', self::getFixtures()),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Equal(): array
    {
        return self::convertFixtures(
            fixtures: [
                [42, 42],
                ['3.14', '3.14'],
                [1.0001, 1.0001],
                [0, -0],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function getFixtures(): array
    {
        return [
            [42, 0],
            [99.9, 3.14],
            [100, 0.123],
            [49.99, 10],
            [1_123, 1122],
            [0o770, 10],
            [0b111, 2],
            [0x1A, 14],
            [PHP_INT_MAX, 0],
            [0, PHP_INT_MIN],
            [PHP_FLOAT_MIN, 0],
            [0, -PHP_FLOAT_MIN],
            ['10', '5'],
            ['31.4', '3.2999'],
            ['10', '9.99999999'],
        ];
    }

    /**
     * @param mixed[] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0], // @phpstan-ignore-line We know this is an array but can't typecast the callable
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            IsNotGreaterThan::ARGUMENT_INDEX_VALUE => $data[1], // @phpstan-ignore-line
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }

    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidType')]
    public function testValidate_Invalid_ArgumentType(
        mixed $argumentValue,
    ): void {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $validator = $this->initialiseTestObject();

        $this->expectException(InvalidValidationArgumentsException::class);
        $validator->validate(
            data: 42,
            arguments: $argumentIteratorFactory->create(
                [
                    IsNotGreaterThan::ARGUMENT_INDEX_VALUE => $argumentValue,
                ],
            ),
        );
    }
}
