<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Validator\IsNumeric;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Add tests for Context extractions
 *
 * @method IsNumeric initialiseTestObject()
 */
#[CoversClass(IsNumeric::class)]
class IsNumericTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsNumeric::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return [
            [null],
            [0],
            [-0],
            [42],
            [-99],
            [3.14],
            [-0.23],
            [PHP_INT_MIN],
            [PHP_INT_MAX],
            [PHP_FLOAT_MIN],
            [PHP_FLOAT_MAX],
            [-PHP_FLOAT_MIN],
            [-PHP_FLOAT_MAX],
            [1_234_567],
            [0777],
            [0o777],
            [0O777],
            [0xFFF],
            [0b010],
            ['0'],
            ['-0'],
            ['42'],
            ['-99'],
            ['3.14'],
            ['-0.23'],
            [(string)PHP_INT_MIN],
            [(string)PHP_INT_MAX],
            [(string)PHP_FLOAT_MIN],
            [(string)PHP_FLOAT_MAX],
            [(string)-PHP_FLOAT_MIN],
            [(string)-PHP_FLOAT_MAX],
            ['0 '],
            [' -99'],
            [' 3.14 '],
            ['0777'],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidType(): array
    {
        return [
            [[42]],
            [(object)[3.14]],
            [false],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_InvalidData(): array
    {
        return [
            ['1_234_567'],
            ['1,234,567'],
            ['1.234.567'],
            ['99-'],
            ['0o777'],
            ['0O777'],
            ['0xFFF'],
            ['0b010'],
        ];
    }
}
