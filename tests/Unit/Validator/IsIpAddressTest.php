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
use Klevu\Pipelines\Validator\IsIpAddress;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(IsIpAddress::class)]
class IsIpAddressTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testIpAddress_Success(): array
    {
        return [
            ['0.0.0.0'],
            ['192.168.0.1'],
            ['10.0.0.1'],
            ['172.16.0.1'],
            ['127.0.0.1'],
            ['255.255.255.255'],
            ['203.0.113.0'],
            ['8.8.8.8'],
            ['64.233.160.0'],
            ['172.217.23.206'],
            ['93.184.216.34'],
            ['198.41.0.4'],
            ['2001:4860:4860::8888'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['2606:2800:220:1:248:1893:25c8:1946'],
            ['fe80::1'],
            ['::1'],
            ['ff02::2'],
            ['2001:0db8:0:0:0:0:2:1'],
            ['2606:2800:220:1:248:1893:25c8:1946'],
            ['2001:db8::ff00:42:8329'],
            ['fe80::202:b3ff:fe1e:8329'],
            ['2001:0db8:1234:5678:90ab:cdef:0123:4567'],
            ['2406:da18:7d08:9500:62ed:20ff:fee3:134c'],
            ['2606:2800:133:42e::4'],
            ['2a00:1450:400c:c09::93'],
            ['2001:19f0:7001:74:540e:1ff:fe92:63f4'],
            ['2600:3c02::f03c:91ff:feae:3c4b'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
            ['2001:0db8:85a3::8a2e:0370:7334'],
            ['2001:0db8::1'],
        ];
    }

    /**
     * @dataProvider dataProvider_testIpAddress_Success
     */
    public function testValidIpAddress_WithSuccess(
        mixed $input,
    ): void {
        $validator = new IsIpAddress();
        $validator->validate($input);
        $this->addToAssertionCount(1);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testIpAddress_WithInvalidType(): array
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
     * @dataProvider dataProvider_testIpAddress_WithInvalidType
     */
    public function testIpAddress_WithInvalidType_Exception(
        mixed $input,
    ): void {
        $validator = new IsIpAddress();

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
    public static function dataProvider_testIpAddress_WithInvalidData(): array
    {
        return [
            ['$'],
            ['$null'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:7334/64'],
            ['2001:0db8:85a3::8a2e:0370:7334/64'],
            ['192.168.0.256'],
            ['2001:0db8:85a3:0000:0000:8a2e:0370:'],
            ['invalid-ip-address'],
            ["@example.com"],
            ['ABCD'],
            ['255.255.255.256'],
            ['192.168'],
            ['1A2.090'],
            ['90.11A'],
            ['12.123.1'],
            ['1'],
        ];
    }

    /**
     * @dataProvider dataProvider_testIpAddress_WithInvalidData
     */
    public function testIpAddress_WithInvalidData_Exception(
        mixed $input,
    ): void {
        $validator = new IsIpAddress();

        $this->expectException(InvalidDataValidationException::class);
        $this->expectExceptionMessage('Data is not valid');
        try {
            $validator->validate($input);
        } catch (ValidationException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Value .* is not a valid IP address/',
                string: $errors[0] ?? '',
            );

            throw $exception;
        }
    }
}
