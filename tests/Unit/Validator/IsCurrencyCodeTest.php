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
use Klevu\Pipelines\Validator\IsCurrencyCode;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsCurrencyCode::class)]
class IsCurrencyCodeTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testCurrencyCode_Success(): array
    {
        return [
            ['GBP'],
            ['JPY'],
            ['USD'],
            ['EUR'],
            ['AUD'],
            ['CAD'],
            ['CHF'],
            ['CNY'],
            ['SEK'],
            ['NZD'],
            ['NOK'],
            ['SGD'],
            ['HKD'],
            ['KRW'],
            ['TRY'],
            ['INR'],
            ['RUB'],
            ['BRL'],
            ['ZAR'],
            ['AED'],
            ['ARS'],
            ['MXN'],
            ['IDR'],
            ['THB'],
            ['MYR'],
            ['SAR'],
            ['QAR'],
            ['EGP'],
            ['DKK'],
            ['PHP'],
            ['PLN'],
            ['HUF'],
            ['CZK'],
            ['ILS'],
            ['CLP'],
            ['TWD'],
            ['PKR'],
            ['UAH'],
            ['VND'],
            ['BDT'],
            ['NGN'],
            ['KES'],
            ['EGP'],
            ['CHF'],
            ['SEK'],
            ['ZAR'],
            ['CAD'],
            ['HKD'],
            ['SGD'],
            ['MXN'],
            ['THB'],
        ];
    }

    /**
     * @dataProvider dataProvider_testCurrencyCode_Success
     */
    public function testValidCurrencyCode_WithSuccess(
        mixed $input,
    ): void {
        $validator = new IsCurrencyCode();
        $validator->validate($input);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testCurrencyCode_WithInvalidType(): array
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
     * @dataProvider dataProvider_testCurrencyCode_WithInvalidType
     */
    public function testCurrencyCode_WithInvalidType_Exception(
        mixed $input,
    ): void {
        $validator = new IsCurrencyCode();

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
    public static function dataProvider_testCurrencyCode_WithInvalidData(): array
    {
        return [
            ['$'],
            ['$null'],
            ['Jpy'],
            ['Usd'],
            ['aud'],
            ["@example.com"],
            ['ABCD'],
            ['A'],
            ['A12'],
            ['1A2'],
            ['11A'],
            ['123'],
            ['AB'],
        ];
    }

    /**
     * @dataProvider dataProvider_testCurrencyCode_WithInvalidData
     */
    public function testCurrencyCode_WithInvalidData_Exception(
        mixed $input,
    ): void {
        $validator = new IsCurrencyCode();

        $this->expectException(InvalidDataValidationException::class);
        $this->expectExceptionMessage('Data is not valid');
        try {
            $validator->validate($input);
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Data is not valid currency code/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
    }
}
