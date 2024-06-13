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
use Klevu\Pipelines\Transformer\StripTags;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test invalid constructor arg
 */
#[CoversClass(StripTags::class)]
class StripTagsTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = StripTags::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Simple_AllowedTags(),
            self::dataProvider_testTransform_Valid_Simple_StripContent(),
            self::dataProvider_testTransform_Valid_Array(),
            self::dataProvider_testTransform_Valid_Array_AllowedTags(),
            self::dataProvider_testTransform_Valid_Array_StripContent(),
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [null, [], null],
                ['foo', [], 'foo'],

                ['<foo', [], ''],
                ['foo<br />', [], 'foo'],
                ['foo<br>', [], 'foo'],

                ['<b>foo</b>', [], 'foo'],
                ['<strong>foo</strong>', [], 'foo'],
                ['<i>foo</i>', [], 'foo'],
                ['<em>foo</em>', [], 'foo'],
                ['<p>foo</p>', [], 'foo'],
                ['<a>foo</a>', [], 'foo'],
                ['<a href="https://www.klevu.com" target="_blank" onclick="bar();">foo</a>', [], 'foo'],

                ['<p>Paragraph 1</p><p>Paragraph 2</p>', [], 'Paragraph 1Paragraph 2'],

                [
                    '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                    [],
                    'foo bar baz.wom bat',
                ],
                [
                    <<<'CONTENT'
                    <p>
                        <strong>foo</strong> bar <em>baz</em>.<br />
                        <a href="#">wom <small>bat</small></a>
                    </p>
                    CONTENT,
                    [],
                    <<<'CONTENT'
                    
                        foo bar baz.
                        wom bat
                    
                    CONTENT,
                ],

                ['<script>alert("foo");</script>', [], 'alert("foo");'],
                ['<script
                    >alert("foo");</
                    script>', [], 'alert("foo");'],
                ['<script type="text/javascript">alert("foo");</script>', [], 'alert("foo");'],
                ['<script data-foo="bar">alert("foo");</script>', [], 'alert("foo");'],
                ['<style>body{background:pink;}</style>', [], 'body{background:pink;}'],
                ['<style type="text/css">body{background:pink;}</style>', [], 'body{background:pink;}'],

                ['<?php phpinfo(); ?>', [], ''],
                ['foo <?php echo "bar"; ?> baz', [], 'foo  baz'],
                ['<!-- foo --> bar', [], ' bar'],

                [
                    <<<'CONTENT'
                    <p>
                        <strong>foo</strong> bar <em>baz</em>.<br />
                        <a href="#">wom <small>bat</small></a>
                    </p>
                    CONTENT,
                    [],
                    <<<'CONTENT'
                    
                        foo bar baz.
                        wom bat
                    
                    CONTENT,
                ],

                [
                    '<p>😁</p>',
                    [],
                    '😁',
                ],
            ],
        );
        // phpcs:enable Generic.Files.LineLength.TooLong
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_AllowedTags(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [
                        ['a'],
                    ],
                    null,
                ],

                ['<strong>foo</strong>', [['b']], 'foo'],
                ['<strong>foo</strong>', [['strong']], '<strong>foo</strong>'],
                ['<strong>foo</strong>', [['b', 'strong']], '<strong>foo</strong>'],
                ['<i>foo</i>', [['i']], '<i>foo</i>'],
                [
                    '<a href="https://www.klevu.com" target="_blank" onclick="bar();">foo</a>',
                    [
                        ['a'],
                    ],
                    '<a href="https://www.klevu.com" target="_blank" onclick="bar();">foo</a>',
                ],
                [
                    '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                    [
                        ['strong'],
                    ],
                    '<strong>foo</strong> bar baz.wom bat',
                ],
                [
                    '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                    [
                        ['p'],
                    ],
                    '<p>foo bar baz.wom bat</p>',
                ],
                [
                    '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                    [
                        ['a'],
                    ],
                    'foo bar baz.<a href="#">wom bat</a>',
                ],
                [
                    '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                    [
                        ['p', 'a', 'strong'],
                    ],
                    '<p><strong>foo</strong> bar baz.<a href="#">wom bat</a></p>',
                ],
                [
                    '<p>Paragraph 1</p><p>Paragraph 2</p>',
                    [
                        ['p'],
                    ],
                    '<p>Paragraph 1</p><p>Paragraph 2</p>',
                ],

                [
                    '<p>😁</p>',
                    [
                        ['p'],
                    ],
                    '<p>😁</p>',
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Simple_StripContent(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [
                    null,
                    [
                        null,
                        ['a'],
                    ],
                    null,
                ],
                [
                    '<script>alert("foo");</script>',
                    [
                        null,
                        ['script'],
                    ],
                    '',
                ],
                [
                    '<p>Paragraph 1</p><p>Paragraph 2</p>',
                    [
                        ['p'],
                        ['p'],
                    ],
                    '<p></p><p></p>',
                ],
                [
                    '<strong>foo <em>bar</em></strong>',
                    [
                        null,
                        ['strong'],
                    ],
                    '',
                ],
                [
                    '<strong>foo <em>bar</em></strong>',
                    [
                        null,
                        ['em'],
                    ],
                    'foo ',
                ],
                [
                    '<strong>foo <em>bar</em></strong>',
                    [
                        ['strong'],
                        ['strong'],
                    ],
                    '<strong></strong>',
                ],
                [
                    '<strong>foo <em>bar</em></strong>',
                    [
                        ['strong'],
                        ['em'],
                    ],
                    '<strong>foo </strong>',
                ],
                [
                    '<strong>foo <em>bar</em></strong>',
                    [
                        ['em'],
                        ['em'],
                    ],
                    'foo <em></em>',
                ],

                [
                    '<p>😁</p>',
                    [
                        ['p'],
                        ['p'],
                    ],
                    '<p></p>',
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
                    [null, 'foo'],
                    [],
                    [null, 'foo'],
                ],
                [
                    ['<foo', 'foo<br />', 'foo<br>'],
                    [],
                    ['', 'foo', 'foo'],
                ],
                [
                    [
                        '<b>foo</b>',
                        '<strong>bar</strong>',
                        '<i>baz</i>',
                        '<em>wom</em>',
                        '<p>bat</p>',
                        '<a>tinky</a>',
                        '<a href="https://www.klevu.com" target="_blank" onclick="bar();">winky</a>',
                    ],
                    [],
                    [
                        'foo',
                        'bar',
                        'baz',
                        'wom',
                        'bat',
                        'tinky',
                        'winky',
                    ],
                ],
                [
                    [
                        '<p><strong>foo</strong> bar <em>baz</em>.<br /><a href="#">wom <small>bat</small></a></p>',
                        <<<'CONTENT'
                        <p>
                            <strong>foo</strong> bar <em>baz</em>.<br />
                            <a href="#">wom <small>bat</small></a>
                        </p>
                        CONTENT,
                    ],
                    [],
                    [
                        'foo bar baz.wom bat',
                        <<<'CONTENT'
                        
                            foo bar baz.
                            wom bat
                        
                        CONTENT,
                    ],
                ],
                [
                    [
                        '<script>alert("foo");</script>',
                        '<script
                        >alert("foo");</
                        script>',
                        '<script type="text/javascript">alert("foo");</script>',
                        '<script data-foo="bar">alert("foo");</script>',
                        '<style>body{background:pink;}</style>',
                        '<style type="text/css">body{background:pink;}</style>',
                    ],
                    [],
                    [
                        'alert("foo");',
                        'alert("foo");',
                        'alert("foo");',
                        'alert("foo");',
                        'body{background:pink;}',
                        'body{background:pink;}',
                    ],
                ],
                [
                    [
                        '<?php phpinfo(); ?>',
                        'foo <?php echo "bar"; ?> baz',
                        '<!-- foo --> bar',
                    ],
                    [],
                    [
                        '',
                        'foo  baz',
                        ' bar',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array_AllowedTags(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [
                    [
                        '<b>foo</b>',
                        '<strong>foo</strong>',
                        '<b><strong>foo</strong></b>',
                        '<strong><b>foo</b></strong>',
                        '<i>foo</i>',
                    ],
                    [
                        ['b'],
                    ],
                    [
                        '<b>foo</b>',
                        'foo',
                        '<b>foo</b>',
                        '<b>foo</b>',
                        'foo',
                    ],
                ],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Array_StripContent(): array
    {
        // phpcs:disable Generic.Files.LineLength.TooLong
        return self::convertFixtures(
            fixtures: [
                [
                    [
                        '<b>foo</b>',
                        '<strong>foo</strong>',
                        '<b><strong>foo</strong> bar</b>',
                        '<strong><b>foo</b> bar</strong>',
                        '<i>foo</i>',
                    ],
                    [
                        null,
                        ['b'],
                    ],
                    [
                        '',
                        'foo',
                        '',
                        ' bar',
                        'foo',
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
                    callback: static fn ($allowedTagsArgumentValue): array => [
                        'foo',
                        [$allowedTagsArgumentValue, null],
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
                array_map(
                    callback: static fn ($stripContentForTagsArgumentValue): array => [
                        'foo',
                        [null, $stripContentForTagsArgumentValue],
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
                    StripTags::ARGUMENT_INDEX_ALLOWED_TAGS => $data[1][0] ?? null,
                    StripTags::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS => $data[1][1] ?? null,
                ])
                    : null,
            ],
            array: $fixtures,
        );
    }
}
