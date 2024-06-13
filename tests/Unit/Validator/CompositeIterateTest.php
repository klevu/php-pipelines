<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use Klevu\Pipelines\Validator\CompositeIterate;
use Klevu\Pipelines\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @todo Add tests for invalid constructor args
 * @todo Add tests for Context extractions
 */
#[CoversClass(CompositeIterate::class)]
class CompositeIterateTest extends TestCase
{
    #[Test]
    public function testImplementsInterface(): void
    {
        $validator = new CompositeIterate();

        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                null,
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsString',
                    ],
                ),
            ],
            [
                [
                    'foo',
                    'bar',
                ],
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsString',
                    ],
                ),
            ],
            [
                [
                    42,
                ],
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsNumeric',
                        'IsPositiveNumber',
                    ],
                ),
            ],
            [
                new TestIterator([
                    'foo',
                    'bar',
                ]),
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsString',
                    ],
                ),
            ],
        ];
    }

    /**
     * @param iterable<mixed>|null $data
     * @param ArgumentIterator $arguments
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testValidate_Valid')]
    public function testValidate_Valid(
        ?iterable $data,
        ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeIterate();

        $errors = [];
        try {
            $validator->validate(
                data: $data,
                arguments: $arguments,
            );
        } catch (ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
                'arguments' => $arguments->toArray(),
            ];
        }

        $this->assertSame([], $errors);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            ['foo'],
            [3.12],
            [true],
            [false],
            [123],
            [new \stdClass()],
        ];
    }
    
    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidType')]
    public function testValidate_InvalidType(
        mixed $data,
    ): void {
        $validator = new CompositeIterate();

        $this->expectException(InvalidTypeValidationException::class);
        $validator->validate($data);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_EmptyValidatorsArgument(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                null,
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [],
                ),
            ],
            [
                new ArgumentIterator(),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [null],
                ),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        [null],
                    ],
                ),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        new ValidationIterator(),
                    ],
                ),
            ],
        ];
    }
    
    #[Test]
    #[DataProvider('dataProvider_testValidate_EmptyValidatorsArgument')]
    public function testValidate_EmptyValidatorsArgument(
        ?ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeIterate();
        
        $exception = null;
        try {
            $validator->validate(
                data: [
                    'foo',
                ],
                arguments: $arguments,
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidValidationArgumentsException::class,
            actual: $exception,
            message: json_encode($arguments) ?: '',
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidValidatorsArgument(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            true,
                        ],
                    ],
                ),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            (object)['foo' => 'bar'],
                        ],
                    ],
                ),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            42,
                        ],
                    ],
                ),
            ],
            [
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            3.14,
                        ],
                    ],
                ),
            ],
        ];
    }


    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidValidatorsArgument')]
    public function testValidate_InvalidValidatorsArgument(
        ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeIterate();

        $exception = null;
        try {
            $validator->validate(
                data: [
                    'foo',
                ],
                arguments: $arguments,
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidValidationArgumentsException::class,
            actual: $exception,
            message: json_encode($arguments) ?: '',
        );
    }

    #[Test]
    public function testValidate_ValidatorNotExists(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $validator = new CompositeIterate();

        $exception = null;
        try {
            $validator->validate(
                data: [
                    'foo',
                ],
                arguments: $argumentIteratorFactory->create(
                    arguments: [
                        'NoSuchValidation',
                    ],
                ),
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidValidationArgumentsException::class,
            actual: $exception,
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    'foo',
                    'bar',
                ],
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            'IsString',
                            'IsEqualTo("foo")',
                        ],
                    ],
                ),
            ],
            [
                [
                    'foo',
                    'bar',
                    42,
                ],
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsString',
                    ],
                ),
            ],
        ];
    }

    /**
     * @param iterable<mixed> $data
     * @param ArgumentIterator $arguments
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidData')]
    public function testValidate_InvalidData(
        iterable $data,
        ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeIterate();

        $exception = null;
        try {
            $validator->validate(
                data: $data,
                arguments: $arguments,
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidDataValidationException::class,
            actual: $exception,
            message: json_encode([
                'data' => $data,
                'arguments' => array_map(
                    static fn (Argument $argument): mixed => $argument->getValue(),
                    $arguments->toArray(),
                ),
            ]) ?: '',
        );
    }
}
