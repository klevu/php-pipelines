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
use Klevu\Pipelines\Model\Transformation\EscapeHtml\Quotes;
use Klevu\Pipelines\Model\Transformation\EscapeHtml\TranslationTables;
use Klevu\Pipelines\Transformer\EscapeHtml;
use PHPUnit\Framework\Attributes\CoversClass;

/**
 * @todo Test quotes conversion argument
 * @todo Test entity translation argument
 * @todo Test double encoding argument
 */
#[CoversClass(EscapeHtml::class)]
class EscapeHtmlTest extends AbstractTransformerTestCase
{
    /**
     * @var string
     */
    protected string $transformerFqcn = EscapeHtml::class;

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_Valid(): array
    {
        return array_merge(
            self::dataProvider_testTransform_Valid_Simple(),
            self::dataProvider_testTransform_Valid_Recursive(),
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
                [true, '1'],
                [false, ''],
                ['null', 'null'],
                ['', ''],
                [' ', ' '],
                ["<a href='test'>Test</a>", "&lt;a href='test'&gt;Test&lt;/a&gt;"],
                [
                    "<strong>&quot;strong & escape&quot;</strong>",
                    "&lt;strong&gt;&quot;strong &amp; escape&quot;&lt;/strong&gt;",
                ],
                [
                    '<b>&quot;strong & single escape&quot;</b>',
                    '&lt;b&gt;&quot;strong &amp; single escape&quot;&lt;/b&gt;',
                ],
                ['<p>Hello\s world</p>', '&lt;p&gt;Hello\s world&lt;/p&gt;'],
                ["Hello\"s'world", "Hello&quot;s'world"],
                ['&', '&amp;'],
                ['&amp;', '&amp;'],
                ['"', '&quot;'],
                ['&quot;', '&quot;'],
                ["'", "'"],
                ['&apos;', '&apos;'],
                ['>', '&gt;'],
                ['&gt;', '&gt;'],
                ['<', '&lt;'],
                ['&lt;', '&lt;'],
                ['&excl;', '&amp;excl;'],
                ['&dollar;', '&amp;dollar;'],
                ['&euro;', '&amp;euro;'],
                [
                    <<<'IDENTIFIER'
                    nowdoc.
                    
                    NL also is preserved.
                    Single ' and " and , and &.
                    IDENTIFIER
                    ,
                    "nowdoc.\n\nNL also is preserved.\nSingle ' and &quot; and , and &amp;.",
                ],
                //['+', '&plus;'],
                //['&plus;', '&plus;'],
                //[',', '&comma;'],
                //['&comma;', '&comma;'],
                //['!', '&excl;'],
                //['$', '&dollar;'],
                //['€', '&euro;'],
            ],
        );
    }

    /**
     * @return mixed[][]
     */
    private static function dataProvider_testTransform_Valid_Recursive(): array
    {
        return self::convertFixtures(
            fixtures: [
                [
                    [null],
                    [null],
                ],
                [
                    [true, false],
                    ['1', ''],
                ],
                [
                    ['null'],
                    ['null'],
                ],
                [
                    ['', ' '],
                    ['', ' '],
                ],
                [
                    ["<a href='test'>Test</a>"],
                    ["&lt;a href='test'&gt;Test&lt;/a&gt;"],
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
        return array_merge(
            self::dataProvider_testTransform_InvalidArguments_Quotes(),
            self::dataProvider_testTransform_InvalidArguments_TranslationTable(),
            self::dataProvider_testTransform_InvalidArguments_DoubleEncoding(),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testTransform_InvalidArguments_Quotes(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                static fn (mixed $quotesArgument): array => self::convertFixtures(
                    fixtures: [
                        ['foo', 'foo'],
                    ],
                    quotes: $quotesArgument,
                    translationTable: TranslationTables::HTML5,
                    allowDoubleEncoding: true,
                ),
                [
                    'foo',
                    true,
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
    public static function dataProvider_testTransform_InvalidArguments_TranslationTable(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                static fn (mixed $translationTableArgument): array => self::convertFixtures(
                    fixtures: [
                        ['foo', 'foo'],
                    ],
                    quotes: Quotes::QUOTES,
                    translationTable: $translationTableArgument,
                    allowDoubleEncoding: true,
                ),
                [
                    'foo',
                    true,
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
    public static function dataProvider_testTransform_InvalidArguments_DoubleEncoding(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return array_merge(
            ...array_map(
                static fn (mixed $allowDoubleEncodingArgument): array => self::convertFixtures(
                    fixtures: [
                        ['foo', 'foo'],
                    ],
                    quotes: Quotes::QUOTES,
                    translationTable: TranslationTables::HTML5,
                    allowDoubleEncoding: $allowDoubleEncodingArgument,
                ),
                [
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
        mixed $quotes = null,
        mixed $translationTable = null,
        mixed $allowDoubleEncoding = null,
    ): array {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return array_map(
            callback: static fn (mixed $data): array => [
                $data[0],
                $data[1],
                $argumentIteratorFactory->create(
                    array_filter(
                        array: [
                            EscapeHtml::ARGUMENT_INDEX_QUOTES => $quotes,
                            EscapeHtml::ARGUMENT_INDEX_TRANSLATION_TABLE => $translationTable,
                            EscapeHtml::ARGUMENT_ALLOW_DOUBLE_ENCODING => $allowDoubleEncoding,
                        ],
                        callback: static fn (mixed $value): bool => (null !== $value),
                    ),
                ),
            ],
            array: $fixtures,
        );
    }
}
