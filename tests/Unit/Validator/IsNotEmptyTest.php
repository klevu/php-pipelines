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
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Validator\IsNotEmpty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsNotEmpty::class)]
class IsNotEmptyTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testNotEmpty_Success(): array
    {
        return [
            [3.12],
            [[true]],
            [true],
            ["Hi, Klevu!"],
            [' '],
            ['single quote \' with UPPER PASSING'],
            [" "],
            [123],
            [['an', 'array']],
            [new \stdClass()],
            ["single quote \" with Title Case Passing"],
        ];
    }

    /**
     * @dataProvider dataProvider_testNotEmpty_Success
     */
    public function testValidNotEmpty_WithSuccess(
        mixed $input,
    ): void {
        $validator = new IsNotEmpty();
        $validator->validate($input);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testNotEmpty_WithInvalidData(): array
    {
        return [
            [""],
            [null],
            [''],
            [false],
        ];
    }

    /**
     * @dataProvider dataProvider_testNotEmpty_WithInvalidData
     */
    public function testNotEmpty_WithInvalidData_Exception(
        mixed $input,
    ): void {
        $validator = new IsNotEmpty();

        $this->expectException(InvalidDataValidationException::class);
        $this->expectExceptionMessage('Data is not valid');
        try {
            $validator->validate($input);
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Data must not be empty/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
    }
}
