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
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Validator\DoesNotMatchRegex;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;

/**
 * @method DoesNotMatchRegex initialiseTestObject()
 */
#[CoversClass(DoesNotMatchRegex::class)]
class DoesNotMatchRegexTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = DoesNotMatchRegex::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, '/foo/'],
                ['foo', '/bar/'],
                ['foobar', '/^foo$/'],
                ['foo', '/FOO/'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            [3.12],
            [true],
            [[true]],
            [false],
            [123],
            [['an', 'array']],
            [new \stdClass()],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo', '/foo/'],
                ['foobar', '#foo#'],
                ['foo', '/FOO/i'],
            ],
        );
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
                $data[0], // @phpstan-ignore-line We know this is an array
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            // @phpstan-ignore-next-line We know this is an array
                            DoesNotMatchRegex::ARGUMENT_INDEX_REGULAR_EXPRESSION => ($data[1] ?? null),
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData_InvalidArgument(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo', 3.12],
                ['foo', true],
                ['foo', [true]],
                ['foo', false],
                ['foo', 123],
                ['foo', ['an', 'array']],
                ['foo', new \stdClass()],

                ['foo', 'foo'],
                ['foo', '/(foo/'],
                ['foo', '/foo/bar/'],
            ],
        );
    }

    #[DataProvider('dataProvider_testValidate_InvalidData_InvalidArgument')]
    public function testValidate_InvalidData_InvalidArgument(
        string $data,
        ArgumentIterator $arguments,
    ): void {
        $validator = new DoesNotMatchRegex();

        $this->expectException(InvalidValidationArgumentsException::class);
        $validator->validate(
            data: $data,
            arguments: $arguments,
        );
    }
}
