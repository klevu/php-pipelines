<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Validator\IsCurrencyCode;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @method IsCurrencyCode initialiseTestObject()
 */
#[CoversClass(IsCurrencyCode::class)]
class IsCurrencyCodeTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsCurrencyCode::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return [
            [null],
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
}
