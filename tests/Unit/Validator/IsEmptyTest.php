<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Validator\IsEmpty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

/**
 * @method IsEmpty initialiseTestObject()
 */
#[CoversClass(IsEmpty::class)]
class IsEmptyTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsEmpty::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return [
            [""],
            [null],
            [''],
            [false],
        ];
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

    #[Test]
    #[TestWith([null])]
    public function testValidate_InvalidType(
        mixed $data, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        $this->markTestSkipped();
    }
}
