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
use Klevu\Pipelines\Transformer\MaxWords;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 * @todo Test changed match word characters
 */
#[CoversClass(MaxWords::class)]
class MaxWordsTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = MaxWords::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Simple_WithTruncationString(),
            self::dataProvider_testTransform_Valid_Array(),
            self::dataProvider_testTransform_Valid_Array_WithTruncationString(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    '',
                    [1000],
                    '',
                ],
                [
                    '  ',
                    [1000],
                    '  ',
                ],
                [
                    ' Foo bar.  Baz',
                    [1000],
                    ' Foo bar.  Baz',
                ],
                [
                    ' Foo-bar. Baz',
                    [1000],
                    ' Foo-bar. Baz',
                ],
                [
                    ' Foo
                    bar. 
                    
                    Baz',
                    [1000],
                    ' Foo
                    bar. 
                    
                    Baz',
                ],

                [
                    '',
                    [2],
                    '',
                ],
                [
                    ' ',
                    [2],
                    ' ',
                ],
                [
                    ' Foo bar.  Baz',
                    [2],
                    ' Foo bar.',
                ],
                [
                    ' Foo-bar.  Baz',
                    [2],
                    ' Foo-bar.  Baz',
                ],
                [
                    ' Foo-bar.Baz',
                    [2],
                    ' Foo-bar.Baz', // Unfortunate consequence of allowing dots in words
                ],
                [
                    ' Foo
                    bar. 
                    
                    Baz',
                    [2],
                    ' Foo
                    bar.',
                ],

                [
                    'www.klevu.com',
                    [1],
                    'www.klevu.com',
                ],
                [
                    'Website: www.klevu.com for more information',
                    [2],
                    'Website: www.klevu.com',
                ],

                [
                    '<p><strong><a href="https://www.klevu.com">foo</a></strong> <em>bar</em></p>
<h1>Baz</h1>',
                    [4],
                    '<p><strong><a href', // HTML should be passed through strip tags first
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_WithTruncationString(): array
    {
        return self::convertFixtures(
            fixtures: [
                [null, [], null],

                [
                    '',
                    [1000, '...'],
                    '',
                ],
                [
                    '  ',
                    [1000, '...'],
                    '  ',
                ],
                [
                    ' Foo bar.  Baz',
                    [1000, '...'],
                    ' Foo bar.  Baz',
                ],
                [
                    ' Foo-bar. Baz',
                    [1000, '...'],
                    ' Foo-bar. Baz',
                ],
                [
                    ' Foo
                    bar. 
                    
                    Baz',
                    [1000, '...'],
                    ' Foo
                    bar. 
                    
                    Baz',
                ],

                [
                    '',
                    [2, '...'],
                    '',
                ],
                [
                    ' ',
                    [2, '...'],
                    ' ',
                ],
                [
                    ' Foo bar.  Baz',
                    [2, '...'],
                    ' Foo bar....',
                ],
                [
                    ' Foo-bar.  Baz',
                    [2, '...'],
                    ' Foo-bar.  Baz',
                ],
                [
                    ' Foo-bar.Baz',
                    [2, '...'],
                    ' Foo-bar.Baz', // Unfortunate consequence of allowing dots in words
                ],
                [
                    ' Foo
                    bar. 
                    
                    Baz',
                    [2, '...'],
                    ' Foo
                    bar....',
                ],

                [
                    'www.klevu.com',
                    [1, '...'],
                    'www.klevu.com',
                ],
                [
                    'Website: www.klevu.com for more information',
                    [2, '...'],
                    'Website: www.klevu.com...',
                ],
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
                    [],
                    [null],
                ],
                [
                    [null, '', '  '],
                    [1000],
                    [null, '', '  '],
                ],
                [
                    [
                        ' Foo bar.  Baz',
                        ' Foo-bar. Baz',
                        ' Foo
                        bar. 
                        
                        Baz',
                    ],
                    [1000],
                    [
                        ' Foo bar.  Baz',
                        ' Foo-bar. Baz',
                        ' Foo
                        bar. 
                        
                        Baz',
                    ],
                ],
                [
                    [
                        '',
                        ' ',
                    ],
                    [2],
                    [
                        '',
                        ' ',
                    ],
                ],
                [
                    [
                        ' Foo bar.  Baz',
                        ' Foo-bar.  Baz',
                        ' Foo-bar.Baz',
                        ' Foo
                        bar. 
                        
                        Baz',
                    ],
                    [2],
                    [
                        ' Foo bar.',
                        ' Foo-bar.  Baz',
                        ' Foo-bar.Baz', // Unfortunate consequence of allowing dots in words
                        ' Foo
                        bar.',
                    ],
                ],
                [
                    [
                        'www.klevu.com',
                        'Website: www.klevu.com for more information',
                    ],
                    [2],
                    [
                        'www.klevu.com',
                        'Website: www.klevu.com',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array_WithTruncationString(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [null],
                    [],
                    [null],
                ],
                [
                    [
                        '',
                        '  ',
                        ' Foo bar.  Baz',
                        ' Foo-bar. Baz',
                        ' Foo
                        bar. 
                        
                        Baz',

                    ],
                    [1000, '...'],

                    [
                        '',
                        '  ',
                        ' Foo bar.  Baz',
                        ' Foo-bar. Baz',
                        ' Foo
                        bar. 
                        
                        Baz',

                    ],
                ],
                [
                    [
                        '',
                        ' ',
                        ' Foo bar.  Baz',
                        ' Foo-bar.  Baz',
                        ' Foo-bar.Baz',
                        ' Foo
                        bar. 
                        
                        Baz',
                        'www.klevu.com',
                        'Website: www.klevu.com for more information',
                    ],
                    [2, '...'],
                    [
                        '',
                        ' ',
                        ' Foo bar....',
                        ' Foo-bar.  Baz',
                        ' Foo-bar.Baz', // Unfortunate consequence of allowing dots in words
                        ' Foo
                        bar....',
                        'www.klevu.com',
                        'Website: www.klevu.com...',
                    ],
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
            [(object)['foo']],
            [$fileHandle],
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

        return self::convertFixtures(
            fixtures: array_merge(
                array_map(
                    callback: static fn ($maxWordsArgumentValue): array => [
                        'foo',
                        [$maxWordsArgumentValue, null],
                        '',
                    ],
                    array: [
                        null,
                        '',
                        0,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
                array_map(
                    callback: static fn ($truncationStringArgumentValue): array => [
                        'foo',
                        [null, $truncationStringArgumentValue],
                        '',
                    ],
                    array: [
                        42,
                        3.14,
                        false,
                        [42],
                        (object)['foo'],
                        $fileHandle,
                    ],
                ),
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
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[2] ?? null,
                is_array($data[1] ?? null)
                    ? $argumentIteratorFactory->create([
                    MaxWords::ARGUMENT_INDEX_MAX_WORDS => $data[1][0] ?? null,
                    MaxWords::ARGUMENT_INDEX_TRUNCATION_STRING => $data[1][1] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
