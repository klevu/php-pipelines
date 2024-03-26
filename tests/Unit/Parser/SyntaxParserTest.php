<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Parser;

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\SyntaxItem;
use Klevu\Pipelines\Model\SyntaxItemIterator;
use Klevu\Pipelines\Parser\SyntaxParser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SyntaxParser::class)]
class SyntaxParserTest extends TestCase
{
    /**
     * @return mixed[][]
     */
//    public static function dataProvider_testParse_Valid(): array
//    {
//        return [
//            [
//                'Trim',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'Trim',
//                        arguments: null,
//                    ),
//                ]),
//            ],
//            [
//                'Trim|ToString',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'Trim',
//                        arguments: null,
//                    ),
//                    new SyntaxItem(
//                        command: 'ToString',
//                        arguments: null,
//                    ),
//                ]),
//            ],
//            [
//                'FormatNumber(2, ".", "")',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'FormatNumber',
//                        arguments: [
//                            2,
//                            '.',
//                            '',
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'Append("(|)",$foo,)|Trim|FormatNumber()',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'Append',
//                        arguments: [
//                            '(|)',
//                            new Extraction(accessor: 'foo'),
//                            null,
//                        ],
//                    ),
//                    new SyntaxItem(
//                        command: 'Trim',
//                        arguments: null,
//                    ),
//                    new SyntaxItem(
//                        command: 'FormatNumber',
//                        arguments: null,
//                    ),
//                ]),
//            ],
//            [
//                'foo_123_ABC_("Append(\"123|456)\")")',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'foo_123_ABC_',
//                        arguments: [
//                            'Append("123|456)")',
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'Append(" - ", $config::bar)',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'Append',
//                        arguments: [
//                            ' - ',
//                            new Extraction(accessor: 'config::bar'),
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'EscapeHtml(,,false)',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'EscapeHtml',
//                        arguments: [
//                            null,
//                            null,
//                            false,
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'FilterCompare([$, "gt", $config::threshold])',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'FilterCompare',
//                        arguments: [
//                            [
//                                new Extraction(accessor: ''),
//                                'gt',
//                                new Extraction(accessor: 'config::threshold'),
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'StripTags(["p"], ["script"])',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'StripTags',
//                        arguments: [
//                            [
//                                'p',
//                            ],
//                            [
//                                'script',
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'ValueMap({"foo": "FOO", "bar": 123.45, "baz": null, "0": false, "1": true, "}": $config::test,
// "{": [], "\\\\"": ""})',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'ValueMap',
//                        arguments: [
//                            [
//                                'foo' => 'FOO',
//                                'bar' => 123.45,
//                                'baz' => true,
//                                '0' => false,
//                                '1' => true,
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'ValueMap({$extractedKey: $extractedValue})',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'ValueMap',
//                        arguments: [
//                            [
//                                'foo' => 'FOO',
//                                'bar' => 123.45,
//                                'baz' => true,
//                                '0' => false,
//                                '1' => true,
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'ValueMap({0|ToString: $extractedValue|FormatNumber(2,,$config::thousands_separator)})',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'ValueMap',
//                        arguments: [
//                            [
//                                '0|ToString' => new Extraction(
//                                    accessor: 'extractedValue',
//                                    transformations: new TransformationIterator([
//                                        new Transformation(
//                                            transformerName: 'FormatNumber',
//                                            arguments: [
//                                                2,
//                                                null,
//                                                new Extraction(
//                                                    accessor: 'config::thousands_separator',
//                                                ),
//                                            ],
//                                        ),
//                                    ]),
//                                ),
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'ValueMap({"foo": $config::valueMap})',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'ValueMap',
//                        arguments: [
//                            [
//                                'foo' => new Extraction(accessor: 'config::valueMap'),
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//            [
//                'ValueMap([{"sourceValue": "foo", "convertedValue": "FOO", "caseSensitive": false},
// {"sourceValue":"bar","convertedValue":"__bar__"}, { "sourceValue" : "123.45" , "convertedValue" :
// "one-two-three-point-four-five" , "strict" : false}])',
//                new SyntaxItemIterator([
//                    new SyntaxItem(
//                        command: 'ValueMap',
//                        arguments: [
//                            [
//                                [
//                                    'sourceValue' => 'foo',
//                                    'convertedValue' => 'FOO',
//                                    'caseSensitive' => false,
//                                ],
//                                [
//                                    'sourceValue' => 'bar',
//                                    'convertedValue' => '__bar__',
//                                ],
//                                [
//                                    'sourceValue' => '123.45',
//                                    'convertedValue' => 'one-two-three-point-four-five',
//                                    'strict' => false,
//                                ],
//                            ],
//                        ],
//                    ),
//                ]),
//            ],
//        ];
//    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testParse_WithoutArguments(): array
    {
        return [
            [
                'Transform',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                ]),
            ],
            [
                'Transform()',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                ]),
            ],
            [
                'Transform|Another_Transform123',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                    new SyntaxItem(
                        command: 'Another_Transform123',
                        arguments: null,
                    ),
                ]),
            ],
            [
                'Transform()|Another_Transform123',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                    new SyntaxItem(
                        command: 'Another_Transform123',
                        arguments: null,
                    ),
                ]),
            ],
            [
                'Transform|Another_Transform123()',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                    new SyntaxItem(
                        command: 'Another_Transform123',
                        arguments: null,
                    ),
                ]),
            ],
            [
                'Transform()|Another_Transform123()',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: null,
                    ),
                    new SyntaxItem(
                        command: 'Another_Transform123',
                        arguments: null,
                    ),
                ]),
            ],
        ];
    }

    /**
     * @param string $syntaxString
     * @param SyntaxItemIterator $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testParse_WithoutArguments')]
    public function testParse_WithoutArguments(
        string $syntaxString,
        SyntaxItemIterator $expectedResult,
    ): void {
        $syntaxParser = new SyntaxParser();

        $actualResult = $syntaxParser->parse($syntaxString);
        $this->assertEquals(
            $expectedResult,
            $actualResult,
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testParse_SimpleArguments(): array
    {
        return [
            [
                'Transform("foo", $bar, 42, 3.14, false, null)',
                new SyntaxItemIterator([
                    new SyntaxItem(
                        command: 'Transform',
                        arguments: new ArgumentIterator([
                            new Argument(
                                value: "foo",
                                key: 0,
                            ),
                            new Argument(
                                value: new Extraction(
                                    accessor: 'bar',
                                    transformations: null,
                                ),
                                key: 1,
                            ),
                            new Argument(
                                value: 42,
                                key: 2,
                            ),
                            new Argument(
                                value: 3.14,
                                key: 3,
                            ),
                            new Argument(
                                value: false,
                                key: 4,
                            ),
                            new Argument(
                                value: null,
                                key: 5,
                            ),
                        ]),
                    ),
                ]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testParse_SimpleArguments')]
    public function testParse_SimpleArguments(
        string $syntaxString,
        SyntaxItemIterator $expectedResult,
    ): void {
        $syntaxParser = new SyntaxParser();

        $actualResult = $syntaxParser->parse($syntaxString);
        $this->assertEquals(
            $expectedResult,
            $actualResult,
        );
    }
}
