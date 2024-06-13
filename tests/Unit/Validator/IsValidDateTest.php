<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Validator;

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Validator\IsValidDate;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @method IsValidDate initialiseTestObject()
 */
#[CoversClass(IsValidDate::class)]
class IsValidDateTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsValidDate::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            [[null]],
            self::dataProvider_testValidate_Valid_Simple(),
            self::dataProvider_testValidate_Valid_WithoutAllowedFormats(),
            self::dataProvider_testValidate_Valid_WithAllowedFormats(),
            self::dataProvider_testValidate_Valid_PhpQuirks(),
        );
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
        return array_merge(
            self::dataProvider_testValidate_InvalidData_Simple(),
            self::dataProvider_testValidate_InvalidData_WithoutAllowedFormats(),
            self::dataProvider_testValidate_InvalidData_WithAllowedFormats(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [''],
                ['2024-01-01'],
                ['2024-01-01 00:00:00'],
                ['2024-01-01T00:00:00.000Z'],
                // ['2024-01-01T00:00:00+02:00'], // @todo as part of timezone support
                ['12/31/2024'], // Month-Day dates are separated by /
                ['31-12-2024'], // Day-Month dats by -. Fun wee PHP quirk, there.
                ['31-12-24'],
                ['1968-01-01'],
                ['0001-01-01'],
                ['9999-12-31'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_WithoutAllowedFormats(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, null],
                ['2024-01-01', null],
                ['2024-1-1', null],
                ['2024-01-01 00:00:00', null],
                ['2024-01-01 24:00:00', null],
                ['2024-01-01T00:00:00.000Z', null],
                ['2024-01-01T00:00:00+02:00', null],
                ['12/31/2024', null], // Month-Day dates are separated by /
                ['31-12-2024', null], // Day-Month dats by -. Fun wee PHP quirk, there.
                ['31-12-24', null],
                ['31st December', null],
                ['December', null], // PHP will evaluate as "today's day of month in December this year at midnight"
                ['1968-01-01', null],
                ['0001-01-01', null],
                ['9999-12-31', null],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_WithAllowedFormats(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, []],
                [null, ['c']],
                ['2024-01-01', ['Y-m-d']],
                ['2024-01-01', ['Y-m-d', 'c']],
                ['2024-01-01 01:00:00', ['Y-m-d H:i:s']],
                ['2024-01-01 01:00:00', ['Y-m-d h:i:s']],
                ['2024-01-01T00:00:00.000Z', ['Y-m-d\TH:i:s.vp']],
                ['2024-01-01T00:00:00+00:00', ['c']],
                // ['2024-01-01T00:00:00+02:00', ['c']], // @todo as part of timezones support
                ['12/31/2024', ['m/d/Y']], // Month-Day dates are separated by /
                ['31-12-2024', ['d-m-Y']], // Day-Month dats by -.
                ['24-12-31', ['y-m-d']], // Where all parts are 2 digit, PHP assumes y-m-d
                ['31st December', ['jS F']],
                ['December', ['F']], // PHP will evaluate as "today's day of month in December this year at midnight"
                ['', ['']],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_PhpQuirks(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['2024-01-01 24:00:01'],
                ['30-2-2024'],
                ['0-0-0'],
                ['2024-00-01'],
                ['2024-01-00'],
                ['10000-01-01'],

                ['2024-01-01 24:00:01', null],
                ['30-2-2024', null],
                ['0-0-0', null],
                ['2024-00-01', null],
                ['2024-01-00', null],
                ['32768-01-01', null],
                ['2024-1-1', null],
                ['2024-01-01 24:00:00', null],
                ['31st December', null],
                ['December', null], // PHP will evaluate as "today's day of month in December this year at midnight"
                ['10000-01-01', null],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo'],
                ['2024.01.01'],
                ['2024-99-99 00:00:00'],
                ['12-31-2024'], // Month-Day dates are separated by /
                ['31/12/2024'], // Day-Month dats by -.
                ['32-12-2024'],
                ['Monday, January 1 2024 at 12pm'],
                ['0000-00-00'],
                ['2024-01-01 25:00:00'],
                ['-2024-01-01'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_WithoutAllowedFormats(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo', null],
                ['2024.01.01', null],
                ['2024-99-99 00:00:00', null],
                ['12-31-2024', null], // Month-Day dates are separated by /
                ['31/12/2024', null], // Day-Month dats by -.
                ['32-12-2024', null],
                ['Monday, January 1 2024 at 12pm', null],
                ['0000-00-00', null],
                ['2024-01-01 25:00:00', null],
                ['-2024-01-01', null],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_WithAllowedFormats(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['foo', ['\f\o\o']], // Just because it's the same format, doesn't mean it's a date
                ['2024.01.01', ['Y.m.d']],
                ['2024-99-99 00:00:00', ['Y-m-d H:i:s']],
                ['12-31-2024', ['m-d-Y']], // Month-Day dates are separated by / (and Day-Month by -) in PHP, so
                ['31/12/2024', ['d/m/Y']], // regardless of format passed, these will not validate

                ['2024-01-01', ['Y-m-d H:i:s']],
                ['2024-01-01 0:00:00', ['Y-m-d h:i:s']], // Cheeky 12 hour time in the format
                ['2024-01-01 13:00:00', ['Y-m-d h:i:s']], // Cheeky 12 hour time in the format
                ['12/31/2024', ['d/m/Y']],
                ['31-12-2024', ['m-d-Y']], // Day-Month dats by -.
                ['31st December', ['jS F Y']],
                // These PHP quirks can be picked up when explicit formats are provided
                ['2024-01-01 24:00:01', ['Y-m-d H:i:s']],
                ['30-2-2024', ['d-M-Y']],
                ['0-0-0', ['y-M-d']],
                ['2024-00-01', ['Y-m-d']],
                ['2024-01-00', ['Y-m-d']],
                ['10000-01-01', ['Y-m-d']],
            ],
        );
    }

    /**
     * @param mixed[] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0], // @phpstan-ignore-line We know this is an array
                $argumentIteratorFactory->create(
                    [
                        // @phpstan-ignore-next-line We know this is an array
                        IsValidDate::ARGUMENT_INDEX_ALLOWED_FORMATS => ($data[1] ?? null),
                    ],
                ),
            ],
            array: $fixtures,
        );
    }
}
