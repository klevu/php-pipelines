<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\ToBoolean;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;

/**
 * @todo Test arguments
 * @todo Test injected true|false values
 */
#[CoversClass(ToBoolean::class)]
class ToBooleanTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ToBoolean::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Enumerated(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, false],
                ['', false],
                [' ', true],
                [true, true],
                [false, false],
                [3.14, true],
                [3, true],
                ['3.14', true],
                ['3', true],
                [0b111, true],
                [0o777, true],
                [0xFFF, true],
                [1_234, true],
                [
                    new class () {
                        public function __toString(): string
                        {
                            return '456';
                        }
                    },
                    true,
                ],
                [
                    new class () implements \Stringable {
                        public function __toString(): string
                        {
                            return '7890';
                        }
                    },
                    true,
                ],
                [[], false],
                [[false], true],
                [(object)[], true],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Enumerated(): array
    {
        return self::convertFixtures(
            fixtures: [
                ['true', true],
                ['TRUE', true],
                [1, true],
                ['1', true],
                ['yes', true],
                ['YES', true],
                ['false', false],
                ['FALSE', false],
                [0, false],
                ['0', false],
                ['no', false],
                ['NO', false],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidInputData(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return [
            [(object)['foo']],
            [$fileHandle],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        return array_merge(
            self::dataProvider_testTransform_InvalidArguments_CaseSensitive(),
            self::dataProvider_testTransform_InvalidArguments_TrimWhitespace(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments_CaseSensitive(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                callback: static fn (mixed $caseSensitiveArgument): array => self::convertFixtures(
                    fixtures: [
                        [
                            'foo',
                            'foo',
                        ],
                    ],
                    caseSensitive: $caseSensitiveArgument,
                ),
                array: [
                    'foo',
                    42,
                    3.14,
                    ['foo'],
                    (object)['foo'],
                    $fileHandle,
                ],
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments_TrimWhitespace(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                callback: static fn (mixed $trimWhitespaceArgument): array => self::convertFixtures(
                    fixtures: [
                        [
                            'foo',
                            'foo',
                        ],
                    ],
                    trimWhitespace: $trimWhitespaceArgument,
                ),
                array: [
                    'foo',
                    42,
                    3.14,
                    ['foo'],
                    (object)['foo'],
                    $fileHandle,
                ],
            ),
        );
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        mixed $caseSensitive = null,
        mixed $trimWhitespace = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $return = array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[1],
                $argumentIteratorFactory->create(
                    arguments: array_filter(
                        array: [
                            ToBoolean::ARGUMENT_INDEX_CASE_SENSITIVE => $caseSensitive,
                            ToBoolean::ARGUMENT_INDEX_TRIM_WHITESPACE => $trimWhitespace,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );

        return $return;
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidInputData')]
    public function testTransform_InvalidInputData(
        mixed $data,
    ): void {
        $this->markTestSkipped();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
