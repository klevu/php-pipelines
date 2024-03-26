<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Validator\IsString;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsString::class)]
class IsStringTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testString_Success(): array
    {
        return [
            [null],
            ["Hi, Klevu!"],
            [''],
            [' '],
            ['single quote \' with UPPER PASSING'],
            [""],
            [" "],
            ["single quote \" with Title Case Passing"],
        ];
    }

    /**
     * @dataProvider dataProvider_testString_Success
     */
    public function testValidString_WithSuccess(
        mixed $input,
    ): void {
        $validator = new IsString();
        $validator->validate($input);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testString_WithInvalidData(): array
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
     * @dataProvider dataProvider_testString_WithInvalidData
     */
    public function testString_WithInvalidData_Exception(
        mixed $input,
    ): void {
        $validator = new IsString();
        $this->expectException(InvalidTypeValidationException::class);
        $this->expectExceptionMessage(
            'Invalid data type received',
        );
        $validator->validate($input);
    }
}
