<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Transformer\ToLowerCase;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;

#[CoversClass(ToLowerCase::class)]
class ToLowerCaseTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = ToLowerCase::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Array(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, null],
                ['FOO', 'foo'],
                ['fOO, Bar. BAZ', 'foo, bar. baz'],
                [' Foo ', ' foo '],
                ['테스트 문자열입니다', '테스트 문자열입니다'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [null],
                    [null],
                ],
                [
                    ['FOO', 'BAR'],
                    ['foo', 'bar'],
                ],
                [
                    ['fOO, Bar. BAZ',],
                    ['foo, bar. baz'],
                ],
                [
                    [' Foo ', ' bAr '],
                    [' foo ', ' bar '],
                ],
                [
                    ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                    ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                ],
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
            [42],
            [3.14],
            [true],
            [(object)['foo']],
            [$fileHandle],

            [[42]],
            [[3.14]],
            [[true]],
            [[(object)['foo']]],
            [[$fileHandle]],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments(): array
    {
        return [];
    }

    /**
     * @param mixed[][] $fixtures
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
    ): array {
        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[1],
            ],
            array: $fixtures,
        );
    }

    // phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    /**
     * @param mixed $data
     * @param mixed|null $expectedResult
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[TestWith([null])]
    public function testTransform_InvalidArguments(
        mixed $data,
        mixed $expectedResult = null,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        $this->markTestSkipped();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
