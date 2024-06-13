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
use Klevu\Pipelines\Validator\IsNotEqualTo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @todo Add tests for Context extractions
 *
 * @method IsNotEqualTo initialiseTestObject()
 */
#[CoversClass(IsNotEqualTo::class)]
class IsNotEqualToTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsNotEqualTo::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            self::dataProvider_testValidate_Valid_Simple(),
            self::dataProvider_testValidate_Valid_Complex(),
            self::dataProvider_testValidate_Valid_Strict(),
            self::dataProvider_testValidate_Valid_Strict_Complex(),
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
            self::dataProvider_testValidate_InvalidData_Simple(),
            self::dataProvider_testValidate_InvalidData_Complex(),
            self::dataProvider_testValidate_InvalidData_Strict(),
            self::dataProvider_testValidate_InvalidData_Strict_Complex(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [false, false],
                [false, null],
                [false, 0],
                [false, []],
                [false, ''],

                ['foo', 'foo'],

                [1, 1],
                [1, '1'],
                [1, 1.0],
                ['1', '1'],
                ['1', true],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Complex(): array
    {
        return self::convertFixtures(
            fixtures: [
                [['foo'], ['foo']],
                [(object)['foo' => 'bar'], (object)['foo' => 'bar']],
                [new TestIterator(['foo']), new TestIterator(['foo'])],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Strict(): array
    {
        return self::convertFixtures(
            fixtures: [
                [false, false],
                ['foo', 'foo'],
            ],
            strict: true,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Strict_Complex(): array
    {
        $testObject = (object)['foo' => 'bar'];
        $testIterator = new TestIterator(['foo']);

        return self::convertFixtures(
            fixtures: [
                [$testObject, $testObject],
                [$testIterator, $testIterator],
            ],
            strict: true,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo', 'Foo'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Complex(): array
    {
        return self::convertFixtures(
            fixtures: [
                [['foo'], ['Foo']],
                [['foo', 'bar'], ['bar', 'foo']],
                [['a' => 'foo'], ['b' => 'foo']],
                [['foo', 'bar'], ['bar', 'foo']],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Strict(): array
    {
        return self::convertFixtures(
            fixtures: [
                [false, null],
                [false, 0],
                [false, []],
                [false, ''],

                ['1', '1.0'],
                ['1.0', '1'],
            ],
            strict: true,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Strict_Complex(): array
    {
        return self::convertFixtures(
            fixtures: [
                [['foo', 'bar'], ['bar', 'foo']],
                [(object)['foo' => 'bar'], (object)['foo' => 'bar']],
                [new TestIterator(['foo']), new TestIterator(['foo'])],
            ],
            strict: true,
        );
    }

    /**
     * @param mixed[] $fixtures
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
                $data[0], // @phpstan-ignore-line We know this is an array but can't typecast the callable
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            IsNotEqualTo::ARGUMENT_INDEX_VALUE => $data[1], // @phpstan-ignore-line
                            IsNotEqualTo::ARGUMENT_INDEX_STRICT => $strict,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: array_values(
                array_map(
                    callback: 'unserialize',
                    array: array_unique(
                        array_map(
                            callback: 'serialize',
                            array: array_merge(
                                $fixtures,
                                array_map(
                                    // Thanks for this, phpstan
                                    callback: static fn (mixed $fixture): array => (
                                        array_reverse(is_array($fixture) ? $fixture : [])
                                    ),
                                    array: $fixtures,
                                ),
                            ),
                        ),
                    ),
                ),
            ),
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
