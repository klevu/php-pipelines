<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use Klevu\Pipelines\Validator\CompositeOr;
use Klevu\Pipelines\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * @todo Add tests for invalid constructor args
 * @todo Add tests for Context extractions
 */
#[CoversClass(CompositeOr::class)]
class CompositeOrTest extends TestCase
{
    #[Test]
    public function testImplementsInterface(): void
    {
        $validator = new CompositeOr();

        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Success(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();
        $validationIteratorFactory = new ValidationIteratorFactory();

        return [
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        $validationIteratorFactory->createFromSyntaxDeclaration(
                            syntaxDeclaration: 'IsString|IsNotEmpty',
                        ),
                    ],
                ),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        $validationIteratorFactory->createFromSyntaxDeclaration(
                            syntaxDeclaration: 'IsPositiveNumber',
                        ),
                        $validationIteratorFactory->createFromSyntaxDeclaration(
                            syntaxDeclaration: 'IsEqualTo("foo")',
                        ),
                    ],
                ),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        new Validation(
                            validatorName: 'IsPositiveNumber',
                        ),
                        new Validation(
                            validatorName: 'IsEqualTo',
                            arguments: $argumentIteratorFactory->create(['foo']),
                        ),
                    ],
                ),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsPositiveNumber',
                        'IsEqualTo("foo")',
                    ],
                ),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            new Validation(
                                validatorName: 'IsPositiveNumber',
                            ),
                        ],
                        [
                            new Validation(
                                validatorName: 'IsString',
                            ),
                            new Validation(
                                validatorName: 'IsEqualTo',
                                arguments: $argumentIteratorFactory->create(['foo']),
                            ),
                        ],
                    ],
                ),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        [
                            'IsPositiveNumber',
                        ],
                        [
                            'IsString',
                            'IsEqualTo("foo")',
                        ],
                    ],
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testValidate_Success')]
    public function testValidate_Success(
        mixed $data,
        ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeOr();

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
                'arguments' => $arguments,
            ];
        }

        $this->assertSame([], $errors);
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
        $validator = new CompositeOr();

        $exception = null;
        try {
            $validator->validate(
                data: 'foo',
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
        $validator = new CompositeOr();

        $exception = null;
        try {
            $validator->validate(
                data: 'foo',
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

        $validator = new CompositeOr();

        $this->expectException(InvalidValidationArgumentsException::class);
        try {
            $validator->validate(
                data: 'foo',
                arguments: $argumentIteratorFactory->create(
                    arguments: [
                        'NoSuchValidation',
                    ],
                ),
            );
        } catch (ValidationException $exception) {
            $this->assertInstanceOf(
                expected: ClassNotFoundException::class,
                actual: $exception->getPrevious(),
            );

            throw $exception;
        }
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                'foo',
                $argumentIteratorFactory->create([
                    'IsEmpty',
                ]),
            ],
            [
                'foo',
                $argumentIteratorFactory->create(
                    arguments: [
                        'IsPositiveNumber',
                        'IsEqualTo("foo")|IsEmpty',
                    ],
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidData')]
    public function testValidate_InvalidData(
        mixed $data,
        ArgumentIterator $arguments,
    ): void {
        $validator = new CompositeOr();
        
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
