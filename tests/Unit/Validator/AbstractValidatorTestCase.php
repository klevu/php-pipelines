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
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Validator\ValidatorInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

abstract class AbstractValidatorTestCase extends TestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = '';

    /**
     * @return mixed[]
     */
    abstract public static function dataProvider_testValidate_Valid(): array;

    /**
     * @return mixed[]
     */
    abstract public static function dataProvider_testValidate_InvalidType(): array;

    /**
     * @return mixed[]
     */
    abstract public static function dataProvider_testValidate_InvalidData(): array;

    #[Test]
    public function testImplementsInterface(): void
    {
        $validator = $this->initialiseTestObject();
        $this->assertInstanceOf(ValidatorInterface::class, $validator);
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testValidate_Valid')]
    public function testValidate_Valid(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        /** @var ValidatorInterface $validator */
        $validator = $this->initialiseTestObject();

        $errors = [];
        try {
            $validator->validate(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
                'arguments' => $arguments?->toArray(),
            ];
        }

        $this->assertSame([], $errors);
    }

    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidType')]
    public function testValidate_InvalidType(
        mixed $data,
    ): void {
        /** @var ValidatorInterface $validator */
        $validator = $this->initialiseTestObject();

        $this->expectException(InvalidTypeValidationException::class);
        $validator->validate($data);
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @param string|null $exceptionMessage
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testValidate_InvalidData')]
    public function testValidate_InvalidData(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
        ?string $exceptionMessage = null,
    ): void {
        /** @var ValidatorInterface $validator */
        $validator = $this->initialiseTestObject();

        $exception = null;
        try {
            $validator->validate(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (ValidationException $exception) {
            if (null !== $exceptionMessage) {
                $this->assertStringContainsString(
                    needle: $exceptionMessage,
                    haystack: $exception->getMessage(),
                );
            }
        }

        $this->assertInstanceOf(
            expected: InvalidDataValidationException::class,
            actual: $exception,
            message: json_encode($data) ?: '',
        );
    }

    /**
     * @return object
     * @throws \LogicException
     */
    protected function initialiseTestObject(): object
    {
        if (!$this->validatorFqcn) {
            throw new \LogicException('validatorFqcn must be defined');
        }

        return new $this->validatorFqcn();
    }
}
