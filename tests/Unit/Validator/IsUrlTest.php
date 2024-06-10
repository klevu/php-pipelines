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
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Validator\IsUrl;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @todo Add tests for Context extractions
 * @todo Add tests for Invalid Arguments
 *
 * @method IsUrl initialiseTestObject()
 */
#[CoversClass(IsUrl::class)]
class IsUrlTest extends AbstractValidatorTestCase
{
    /**
     * @var string
     */
    protected string $validatorFqcn = IsUrl::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testValidate_Valid(): array
    {
        return array_merge(
            self::dataProvider_testValidate_Valid_Simple(),
            self::dataProvider_testValidate_Valid_NotRequireProtocol(),
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
            self::dataProvider_testValidate_InvalidData_NotRequireProtocol(),
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
                ['http://www.klevu.com'],
                ['https://www.klevu.com'],
                ['ftp://www.klevu.com'],
                ['ftps://www.klevu.com'],
                ['tcp://www.klevu.com'],
                ['ssh://www.klevu.com'],
                ['https://sub-domain1.example.xyz/abc-123?foo=bar#baz'],
                ['https://' . str_repeat('a', 63) . '.' . str_repeat('a', 63) . '.com'],
                ['https://' . str_repeat('a.', 125) . 'com'],
                // To come with multibyte support
                // ['https://उदाहरण.com/'],
                // ['https://www.klevu.com/उदाहरण'],
                // ['https://www.klevu.com?foo=उदाहरण'],
                // ['https://www.klevu.com#उदाहरण'],
                ['https://www.klevu.com:9001'],
                ['https://www.klevu.com:9001/foo'],
                ['https://www.klevu.com:9001?foo'],
                ['https://www.klevu.com:9001#foo'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_Valid_NotRequireProtocol(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null],
                ['klevu.com'],
                ['www.klevu.com'],
                ['www.klevu.com?'],
                ['www.klevu.com?foo'],
                ['www.klevu.com?foo=bar'],
                ['www.klevu.com/baz/abc-123'],
                ['www.klevu.com/baz/abc-123/'],
                ['www.klevu.com/baz/abc-123/?'],
                ['www.klevu.com/baz/abc-123?foo=bar'],
                ['www.klevu.com/baz/abc-123/?foo=bar'],
                ['www.klevu.com/baz/abc-123/?foo=bar&wom=bat'],
                ['www.klevu.com#'],
                ['www.klevu.com#abc-123'],
                ['sub-domain1.example.xyz/abc-123?foo=bar#baz'],
                ['http://www.klevu.com'],
                ['https://www.klevu.com'],
                ['ftp://www.klevu.com'],
                ['ftps://www.klevu.com'],
                ['ssh://www.klevu.com'],
                ['https://sub-domain1.example.xyz/abc-123?foo=bar#baz'],
                [str_repeat('a', 63) . '.' . str_repeat('a', 63) . '.com'],
                [str_repeat('a.', 125) . 'com'],
                ['https://' . str_repeat('a', 63) . '.' . str_repeat('a', 63) . '.com'],
                ['https://' . str_repeat('a.', 125) . 'com'],
                ['https://www.klevu.com'],
            ],
            requireProtocol: false,
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['klevu.com'],
                ['www.klevu.com'],
                ['www.klevu..com'],
                ['www klevu.com'],
                ['www.klevu.com?'],
                ['www.klevu.com?foo'],
                ['www.klevu.com?foo=bar'],
                ['www.klevu.com/baz/abc-123'],
                ['www.klevu.com/baz/abc-123/'],
                ['www.klevu.com/baz/abc-123/?'],
                ['www.klevu.com/baz/abc-123?foo=bar'],
                ['www.klevu.com/baz/abc-123/?foo=bar'],
                ['www.klevu.com/baz/abc-123/?foo=bar&wom=bat'],
                ['www.klevu.com#'],
                ['www.klevu.com#abc-123'],
                ['sub-domain1.example.xyz/abc-123?foo=bar#baz'],
                ['$'],
                ["contact@klevu.com"],
                ['Foo'],
                ['https://' . str_repeat('a', 64) . '.com'],
                [
                    'https://'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.com',
                ],
                ['https://' . str_repeat('a.', 127) . '.com'],
                ['127.0.0.1'],
                ['https://http://www.klevu.com'],
                ['https:////www.klevu.com'],
                ['ftp://foo:bar@www.klevu.com:9001'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testValidate_InvalidData_NotRequireProtocol(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['$'],
                ['127.0.0.1'],
                ["contact@klevu.com"],
                ['Foo'],
                [str_repeat('a', 250) . '.com'],
                [str_repeat('a', 64) . str_repeat('a', 64) . '.com'],
                [str_repeat('a.', 501) . '.com'],
                ['https://' . str_repeat('a', 250) . '.com'],
                ['https://' . str_repeat('a', 64) . str_repeat('a', 64) . '.com'],
                [
                    'https://'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.'
                    . str_repeat('a', 63) . '.com',
                ],
                ['https://' . str_repeat('a.', 127) . '.com'],
                ['https://http://www.klevu.com'],
                ['https:////www.klevu.com'],
            ],
            requireProtocol: false,
        );
    }

    /**
     * @param mixed[] $fixtures
     * @param bool|null $requireProtocol
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        ?bool $requireProtocol = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0], // @phpstan-ignore-line We know this is an array
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL => $requireProtocol,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }

    /**
     * @param string $data
     * @param string[] $supportedProtocols
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[TestWith(['abc://www.klevu.com', ['abc']])]
    #[TestWith(['//www.klevu.com', ['']])]
    public function testValidate_Valid_SupportedProtocols(
        string $data,
        array $supportedProtocols,
    ): void {
        $validator = new IsUrl(
            supportedProtocols: $supportedProtocols,
        );

        $errors = [];
        try {
            $validator->validate(
                data: $data,
            );
        } catch (ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ];
        }

        $this->assertSame([], $errors);
    }

    /**
     * @param string $data
     * @param string[] $supportedProtocols
     *
     * @return void
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    #[Test]
    #[TestWith(['abc://www.klevu.com', ['https']])]
    #[TestWith(['//www.klevu.com', ['https']])]
    #[TestWith(['https://www.klevu.com', ['http']])]
    #[TestWith(['https://www.klevu.com', []])]
    #[TestWith(['www.klevu.com', []])]
    public function testValidate_InvalidData_SupportedProtocols(
        string $data,
        array $supportedProtocols,
    ): void {
        $validator = new IsUrl(
            supportedProtocols: $supportedProtocols,
        );

        $exception = null;
        try {
            $validator->validate(
                data: $data,
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidDataValidationException::class,
            actual: $exception,
            message: json_encode($data) ?: '',
        );
    }

    #[Test]
    #[TestWith(['https://foo@klevu.com'])]
    #[TestWith(['ftp://foo:bar@klevu.com'])]
    #[TestWith(['ssh://foo:bar@klevu.com:9001'])]
    public function testValidate_Valid_AllowAuthorization(string $data): void
    {
        $validator = new IsUrl(
            allowAuthorization: true,
        );

        $errors = [];
        try {
            $validator->validate(
                data: $data,
            );
        } catch (ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ];
        }

        $this->assertSame([], $errors);
    }

    #[Test]
    #[TestWith(['https://foo@klevu.com'])]
    #[TestWith(['ftp://foo:bar@klevu.com'])]
    #[TestWith(['ssh://foo:bar@klevu.com:9001'])]
    public function testValidate_InvalidData_NotAllowAuthorization(string $data): void
    {
        $validator = new IsUrl(
            allowAuthorization: false,
        );

        $exception = null;
        try {
            $validator->validate(
                data: $data,
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidDataValidationException::class,
            actual: $exception,
            message: json_encode($data) ?: '',
        );
    }

    #[Test]
    #[TestWith(['klevu.com'])]
    #[TestWith(['www.klevu.com'])]
    #[TestWith(['www.klevu.com?'])]
    #[TestWith(['www.klevu.com?foo'])]
    #[TestWith(['www.klevu.com?foo=bar'])]
    #[TestWith(['www.klevu.com/baz/abc-123'])]
    #[TestWith(['www.klevu.com/baz/abc-123/'])]
    #[TestWith(['www.klevu.com/baz/abc-123/?'])]
    #[TestWith(['www.klevu.com/baz/abc-123?foo=bar'])]
    #[TestWith(['www.klevu.com/baz/abc-123/?foo=bar'])]
    #[TestWith(['www.klevu.com/baz/abc-123/?foo=bar&wom=bat'])]
    #[TestWith(['www.klevu.com#'])]
    #[TestWith(['www.klevu.com#abc-123'])]
    #[TestWith(['sub-domain1.example.xyz/abc-123?foo=bar#baz'])]
    public function testValidate_Valid_NoSupportedProtocols_NotRequireProtocol(string $data): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $validator = new IsUrl(
            supportedProtocols: [],
        );

        $errors = [];
        try {
            $validator->validate(
                data: $data,
                arguments: $argumentIteratorFactory->create(
                    [
                        IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL => false,
                    ],
                ),
            );
        } catch (ValidationException $exception) {
            $errors = [
                'data' => $data,
                'exception' => $exception->getMessage(),
                'errors' => $exception->getErrors(),
            ];
        }

        $this->assertSame([], $errors);
    }

    #[Test]
    #[TestWith(['https://klevu.com'])]
    #[TestWith(['https://www.klevu.com'])]
    #[TestWith(['https://www.klevu.com?'])]
    #[TestWith(['https://www.klevu.com?foo'])]
    #[TestWith(['https://www.klevu.com?foo=bar'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123/'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123/?'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123?foo=bar'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123/?foo=bar'])]
    #[TestWith(['https://www.klevu.com/baz/abc-123/?foo=bar&wom=bat'])]
    #[TestWith(['https://www.klevu.com#'])]
    #[TestWith(['https://www.klevu.com#abc-123'])]
    #[TestWith(['https://sub-domain1.example.xyz/abc-123?foo=bar#baz'])]
    public function testValidate_InvalidData_NoSupportedProtocols_NotRequireProtocol(string $data): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $validator = new IsUrl(
            supportedProtocols: [],
        );

        $exception = null;
        try {
            $validator->validate(
                data: $data,
                arguments: $argumentIteratorFactory->create(
                    [
                        IsUrl::ARGUMENT_INDEX_REQUIRE_PROTOCOL => false,
                    ],
                ),
            );
        } catch (ValidationException $exception) { // phpcs:ignore Generic.CodeAnalysis.EmptyStatement.DetectedCatch
        }

        $this->assertInstanceOf(
            expected: InvalidDataValidationException::class,
            actual: $exception,
            message: json_encode($data) ?: '',
        );
    }
}
