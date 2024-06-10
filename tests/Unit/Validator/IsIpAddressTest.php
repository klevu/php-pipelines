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
use Klevu\Pipelines\Model\Validation\IsIpAddress\Versions;
use Klevu\Pipelines\Validator\IsIpAddress;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Add tests for Context extractions
 * @todo Add tests for Invalid Arguments
 *
 * @method IsIpAddress initialiseTestObject()
 */
#[CoversClass(IsIpAddress::class)]
class IsIpAddressTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsIpAddress::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            self::dataProvider_testValidate_Valid_Simple(),
            self::dataProvider_testValidate_Valid_AllowVersions(),
            self::dataProvider_testValidate_Valid_DisallowPrivate(),
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
            self::dataProvider_testValidate_InvalidData_AllowVersions(),
            self::dataProvider_testValidate_InvalidData_DisallowPrivate(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null],
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
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_AllowVersions(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    '127.0.0.1',
                    [Versions::IPv4],
                ],
                [
                    '::1',
                    [Versions::IPv6],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_DisallowPrivate(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['203.0.113.0'],
                ['8.8.8.8'],
                ['64.233.160.0'],
                ['172.217.23.206'],
                ['93.184.216.34'],
                ['198.41.0.4'],
                ['2001:4860:4860::8888'],
                ['2606:2800:220:1:248:1893:25c8:1946'],
                ['ff02::2'],
                ['2606:2800:220:1:248:1893:25c8:1946'],
                ['2406:da18:7d08:9500:62ed:20ff:fee3:134c'],
                ['2606:2800:133:42e::4'],
                ['2a00:1450:400c:c09::93'],
                ['2001:19f0:7001:74:540e:1ff:fe92:63f4'],
                ['2600:3c02::f03c:91ff:feae:3c4b'],
            ],
            allowPrivateAndReserved: false,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
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
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_AllowVersions(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    '127.0.0.1',
                    [Versions::IPv6],
                ],
                [
                    '2001:0db8::1',
                    [Versions::IPv4],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_DisallowPrivate(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['0.0.0.0'],
                ['192.168.0.1'],
                ['10.0.0.1'],
                ['172.16.0.1'],
                ['127.0.0.1'],
                ['255.255.255.255'],
                ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
                ['fe80::1'],
                ['::1'],
                ['2001:0db8:0:0:0:0:2:1'],
                ['2001:db8::ff00:42:8329'],
                ['fe80::202:b3ff:fe1e:8329'],
                ['2001:0db8:1234:5678:90ab:cdef:0123:4567'],
                ['2001:0db8:85a3:0000:0000:8a2e:0370:7334'],
                ['2001:0db8:85a3::8a2e:0370:7334'],
                ['2001:0db8::1'],
            ],
            allowPrivateAndReserved: false,
        );
    }

    /**
     * @param mixed[] $fixtures
     * @param bool|null $allowPrivateAndReserved
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        ?bool $allowPrivateAndReserved = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0], // @phpstan-ignore-line We know this is an array but can't typecast the callable
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            IsIpAddress::ARGUMENT_INDEX_ALLOW_VERSIONS => $data[1] ?? null, // @phpstan-ignore-line
                            IsIpAddress::ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED => $allowPrivateAndReserved,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }
}
