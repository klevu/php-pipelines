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
use Klevu\Pipelines\Validator\IsEmail;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsEmail::class)]
class IsEmailTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testEmail_Success(): array
    {
        return [
            ['test@domain.uk'],
            ['test@example.com'],
            ['test@example2.com'],
            ['test@domain.net'],
            ['test@gmail.com'],
            ['test@domain.com'],
            ['test@nobugmail.com'],
            ['test@hotmail.com'],
            ['test@outlook.com'],
            ['test@yahoo.com'],
            ['test@mxfuel.com'],
            ['test@cellurl.com'],
            ['test@10minutemail.com'],
            ["test+example@gmail.com"],
            ["test+example@example.com"],
        ];
    }

    /**
     * @dataProvider dataProvider_testEmail_Success
     */
    public function testValidEmail_WithSuccess(
        mixed $input,
    ): void {
        $validator = new IsEmail();
        $validator->validate($input);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testEmail_WithInvalidType(): array
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
     * @dataProvider dataProvider_testEmail_WithInvalidType
     */
    public function testEmail_WithInvalidType_Exception(
        mixed $input,
    ): void {
        $validator = new IsEmail();

        $this->expectException(InvalidTypeValidationException::class);
        $this->expectExceptionMessage('Invalid data type received');
        try {
            $validator->validate($input);
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Data must be null\|string/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testEmail_WithInvalidData(): array
    {
        return [
            ['@example.com'],
            ['foo@.com'],
            ['foo bar'],
        ];
    }

    /**
     * @dataProvider dataProvider_testEmail_WithInvalidData
     */
    public function testEmail_WithInvalidData_Exception(
        mixed $input,
    ): void {
        $validator = new IsEmail();

        $this->expectException(InvalidDataValidationException::class);
        $this->expectExceptionMessage('Data is not valid');
        try {
            $validator->validate($input);
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Data must be valid email/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
    }
}
