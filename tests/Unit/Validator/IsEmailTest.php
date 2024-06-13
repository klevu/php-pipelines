<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Validator\IsEmail;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @method IsEmail initialiseTestObject()
 */
#[CoversClass(IsEmail::class)]
class IsEmailTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsEmail::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return [
            [null],
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
        return [
            ['@example.com'],
            ['foo@.com'],
            ['foo bar'],
        ];
    }
}
