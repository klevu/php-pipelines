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
use Klevu\Pipelines\Model\Transformation\ChangeCase\Cases;
use Klevu\Pipelines\Transformer\ChangeCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ChangeCase::class)]
class ChangeCaseTest extends AbstractTransformerTestCase
{
    protected string $transformerFqcn = ChangeCase::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_LowerCase(),
            self::dataProvider_testTransform_Valid_LowerCase_Array(),
            self::dataProvider_testTransform_Valid_TitleCase(),
            self::dataProvider_testTransform_Valid_TitleCase_Array(),
            self::dataProvider_testTransform_Valid_UpperCase(),
            self::dataProvider_testTransform_Valid_UpperCase_Array(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_LowerCase(): array
    {
        return array_merge(
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['FOO', 'foo'],
                    ['fOO, Bar. BAZ', 'foo, bar. baz'],
                    [' Foo ', ' foo '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: Cases::LOWERCASE,
            ),
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['FOO', 'foo'],
                    ['fOO, Bar. BAZ', 'foo, bar. baz'],
                    [' Foo ', ' foo '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: 'lowercase',
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_LowerCase_Array(): array
    {
        return array_merge(
            self::convertFixtures(
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
                case: Cases::LOWERCASE,
            ),
            self::convertFixtures(
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
                case: 'lowercase',
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_TitleCase(): array
    {
        return array_merge(
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['foo', 'Foo'],
                    [' Foo ', ' Foo '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: Cases::TITLECASE,
            ),
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['foo', 'Foo'],
                    [' Foo ', ' Foo '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: 'titlecase',
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_TitleCase_Array(): array
    {
        return array_merge(
            self::convertFixtures(
                fixtures: [
                    [
                        [null],
                        [null],
                    ],
                    [
                        ['foo', 'bar'],
                        ['Foo', 'Bar'],
                    ],
                    [
                        ['fOO, Bar. BAZ',],
                        ['Foo, Bar. Baz'],
                    ],
                    [
                        [' Foo ', ' bAr '],
                        [' Foo ', ' Bar '],
                    ],
                    [
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                    ],
                ],
                case: Cases::TITLECASE,
            ),
            self::convertFixtures(
                fixtures: [
                    [
                        [null],
                        [null],
                    ],
                    [
                        ['foo', 'bar'],
                        ['Foo', 'Bar'],
                    ],
                    [
                        ['fOO, Bar. BAZ',],
                        ['Foo, Bar. Baz'],
                    ],
                    [
                        [' Foo ', ' bAr '],
                        [' Foo ', ' Bar '],
                    ],
                    [
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                    ],
                ],
                case: 'titlecase',
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_UpperCase(): array
    {
        return array_merge(
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['foo', 'FOO'],
                    ['fOO, Bar. BAZ', 'FOO, BAR. BAZ'],
                    [' Foo ', ' FOO '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: Cases::UPPERCASE,
            ),
            self::convertFixtures(
                fixtures: [
                    [null, null],
                    ['foo', 'FOO'],
                    ['fOO, Bar. BAZ', 'FOO, BAR. BAZ'],
                    [' Foo ', ' FOO '],
                    ['테스트 문자열입니다', '테스트 문자열입니다'],
                ],
                case: 'uppercase',
            ),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_UpperCase_Array(): array
    {
        return array_merge(
            self::convertFixtures(
                fixtures: [
                    [
                        [null],
                        [null],
                    ],
                    [
                        ['foo', 'bar'],
                        ['FOO', 'BAR'],
                    ],
                    [
                        ['fOO, Bar. BAZ',],
                        ['FOO, BAR. BAZ'],
                    ],
                    [
                        [' Foo ', ' bAr '],
                        [' FOO ', ' BAR '],
                    ],
                    [
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                    ],
                ],
                case: Cases::UPPERCASE,
            ),
            self::convertFixtures(
                fixtures: [
                    [
                        [null],
                        [null],
                    ],
                    [
                        ['foo', 'bar'],
                        ['FOO', 'BAR'],
                    ],
                    [
                        ['fOO, Bar. BAZ',],
                        ['FOO, BAR. BAZ'],
                    ],
                    [
                        [' Foo ', ' bAr '],
                        [' FOO ', ' BAR '],
                    ],
                    [
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                        ['테스트 문자열입니다', 'این یک رشته آزمایشی است'],
                    ],
                ],
                case: 'uppercase',
            ),
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
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                callback: static fn (mixed $caseArgument): array => self::convertFixtures(
                    fixtures: [
                        [
                            'foo',
                            'foo',
                        ],
                    ],
                    case: $caseArgument,
                ),
                array: [
                    'foo',
                    42,
                    3.14,
                    null,
                    false,
                    [],
                    (object)['foo' => 'bar'],
                    $fileHandle,
                ],
            ),
        );
    }

    /**
     * @param mixed[][] $fixtures
     * @param mixed|null $case
     *
     * @return mixed[][]
     */
    private static function convertFixtures(
        array $fixtures,
        mixed $case = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[1],
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            ChangeCase::ARGUMENT_INDEX_CASE => $case,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }
}
