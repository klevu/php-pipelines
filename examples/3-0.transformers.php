<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\ChangeCase\Cases;
use Klevu\Pipelines\Transformer\Append;
use Klevu\Pipelines\Transformer\ChangeCase;
use Klevu\Pipelines\Transformer\EscapeHtml;
use Klevu\Pipelines\Transformer\FilterCompare;
use Klevu\Pipelines\Transformer\FirstItem;
use Klevu\Pipelines\Transformer\FormatNumber;
use Klevu\Pipelines\Transformer\Join;
use Klevu\Pipelines\Transformer\MapProperty;
use Klevu\Pipelines\Transformer\Max;
use Klevu\Pipelines\Transformer\MaxWords;
use Klevu\Pipelines\Transformer\Min;
use Klevu\Pipelines\Transformer\Prepend;
use Klevu\Pipelines\Transformer\Split;
use Klevu\Pipelines\Transformer\StripTags;
use Klevu\Pipelines\Transformer\ToDateString;
use Klevu\Pipelines\Transformer\ToLowerCase;
use Klevu\Pipelines\Transformer\ToString;
use Klevu\Pipelines\Transformer\ToTitleCase;
use Klevu\Pipelines\Transformer\ToUpperCase;
use Klevu\Pipelines\Transformer\Trim;
use Klevu\Pipelines\Transformer\Unique;
use Klevu\Pipelines\Transformer\ValueMap;

## Append
$transformer = new Append();
// "foo - baz"
$result = $transformer->transform(
    data: 'foo',
    arguments: new ArgumentIterator([
        new Argument(' - '),
        new Argument(
            new Extraction('config::bar'),
        ),
    ]),
    context: [
        'config' => [
            'bar' => 'baz',
        ],
    ],
);
echo '# Append' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Change Case
$transformer = new ChangeCase();
// FOO BAR
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: new ArgumentIterator([
        new Argument(
            value: Cases::UPPERCASE,
            key: ChangeCase::ARGUMENT_INDEX_CASE,
        ),
    ]),
    context: [],
);
echo '# ChangeCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## EscapeHtml
$transformer = new EscapeHtml();
// &quot;&lt;strong&gt;&quot;strong &amp; test&quot;&lt;/strong&gt;&quot;;
$result = $transformer->transform(
    data: '"<strong>&quot;strong & test&quot;</strong>";',
    arguments: new ArgumentIterator([
        new Argument(
            value: false,
            key: EscapeHtml::ARGUMENT_ALLOW_DOUBLE_ENCODING,
        ),
    ]),
    context: [],
);
echo '# EscapeHtml' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## FilterCompare
$transformer = new FilterCompare();
//  [
//      0 => 99,
//      1 => 42,
//      4 => 10025,
//  ]
$result = $transformer->transform(
    data: [
        99,
        42,
        3.14,
        -17,
        10025,
        9.99,
    ],
    arguments: new ArgumentIterator([
        new Argument(
            new ArgumentIterator([
                new Argument(
                    new Extraction(''),
                ),
                new Argument('gt'),
                new Argument(
                    new Extraction('config::threshold'),
                ),
            ]),
        ),
    ]),
    context: [
        'config' => [
            'threshold' => 10,
        ],
    ],
);
echo '# FilterCompare' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## FirstItem
$transformer = new FirstItem();
// 99
$result = $transformer->transform(
    data: [
        99,
        42,
        3.14,
        -17,
        10025,
        9.99,
    ],
    arguments: null,
    context: [],
);
echo '# FirstItem' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## FormatNumber
$transformer = new FormatNumber();
// 12_345::68
$result = $transformer->transform(
    data: 12345.67890,
    arguments: new ArgumentIterator([
        new Argument(
            value: 2,
            key: FormatNumber::ARGUMENT_INDEX_DECIMALS,
        ),
        new Argument(
            value: new Extraction('config::decimal_separator'),
            key: FormatNumber::ARGUMENT_INDEX_DECIMAL_SEPARATOR,
        ),
        new Argument(
            value: '_',
            key: FormatNumber::ARGUMENT_INDEX_THOUSANDS_SEPARATOR,
        ),
    ]),
    context: [
        'config' => [
            'decimal_separator' => '::',
        ],
    ],
);
echo '# FormatNumber' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Join
$transformer = new Join();
// Foo:::Bar:::Baz
$result = $transformer->transform(
    data: [
        'Foo',
        'Bar',
        'Baz',
    ],
    arguments: new ArgumentIterator([
        new Argument(
            value: new Extraction('config::separator'),
            key: Join::ARGUMENT_INDEX_SEPARATOR,
        ),
    ]),
    context: [
        'config' => [
            'separator' => ':::',
        ],
    ],
);
echo '# Join' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## MapProperty
$transformer = new MapProperty();
//  [
//      "Bar",
//      "Value",
//  ]
$result = $transformer->transform(
    data: [
        [
            'key' => 'Foo',
            'value' => 'Bar',
        ],
        [
            'key' => 'Key',
            'value' => 'Value',
        ],
    ],
    arguments: new ArgumentIterator([
        new Argument(
            value: 'value',
            key: MapProperty::ARGUMENT_INDEX_ACCESSOR,
        ),
    ]),
    context: [],
);
echo '# MapProperty' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Max
$transformer = new Max();
// 10025
$result = $transformer->transform(
    data: [
        99,
        42,
        3.14,
        -17,
        10025,
        9.99,
    ],
    arguments: null,
    context: [],
);
echo '# Max' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## MaxWords
$transformer = new MaxWords();
// This is a string[...]
$result = $transformer->transform(
    data: 'This is a string containing ten words as an example',
    arguments: new ArgumentIterator([
        new Argument(
            value: new Extraction('config::max_words'),
            key: MaxWords::ARGUMENT_INDEX_MAX_WORDS,
        ),
        new Argument(
            value: '[...]',
            key: MaxWords::ARGUMENT_INDEX_TRUNCATION_STRING,
        ),
    ]),
    context: [
        'config' => [
            'max_words' => 4,
        ],
    ],
);
echo '# MaxWords' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Min
$transformer = new Min();
// -17
$result = $transformer->transform(
    data: [
        99,
        42,
        3.14,
        -17,
        10025,
        9.99,
    ],
    arguments: null,
    context: [],
);
echo '# Min' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Prepend
$transformer = new Prepend();
// " - bazfoo"
$result = $transformer->transform(
    data: 'foo',
    arguments: new ArgumentIterator([
        new Argument(' - '),
        new Argument(
            new Extraction('config::bar'),
        ),
    ]),
    context: [
        'config' => [
            'bar' => 'baz',
        ],
    ],
);
echo '# Prepend' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Split
$transformer = new Split();
//  [
//      "Parts",
//      "of;a",
//      "string;:;to split",
//      "into parts",
//      "",
//  ]
$result = $transformer->transform(
    data: 'Parts;;of;a;;string;:;to split;;into parts;;',
    arguments: new ArgumentIterator([
        new Argument(
            value: new Extraction('config::separator'),
            key: Split::ARGUMENT_INDEX_SEPARATOR,
        ),
    ]),
    context: [
        'config' => [
            'separator' => ';;',
        ],
    ],
);
echo '# Split' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## StripTags
$transformer = new StripTags();
// <p>This is some text. It contains a nasty  script tag.</p>
$result = $transformer->transform(
    data: '<p>This is some <strong>text</strong>. It contains a nasty <script>alert("Bad things");</script> script tag.</p>',
    arguments: new ArgumentIterator([
        new Argument(
            value: new ArgumentIterator([
                new Argument('p'),
            ]),
            key: StripTags::ARGUMENT_INDEX_ALLOWED_TAGS,
        ),
        new Argument(
            value: new ArgumentIterator([
                new Argument('script'),
            ]),
            key: StripTags::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS,
        ),
    ]),
    context: [],
);
echo '# StripTags' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToDateString
$transformer = new ToDateString();
// Wednesday, 17th May at 7:45 pm
$result = $transformer->transform(
    data: '2023-05-17T18:45+00:00',
    arguments: new ArgumentIterator([
        new Argument(
            value: new Extraction('config::date_format'),
            key: ToDateString::ARGUMENT_INDEX_FORMAT,
        ),
        new Argument(
            value: 'Europe/London',
            key: ToDateString::ARGUMENT_INDEX_TO_TIMEZONE,
        ),
    ]),
    context: [
        'config' => [
            'date_format' => 'l, jS M \a\t g:i a',
        ],
    ],
);
echo '# ToDateString' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToLowerCase
$transformer = new ToLowerCase();
// foo bar
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: null,
    context: [],
);
echo '# ToLowerCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToString
$transformer = new ToString();
// "123.45"
$result = $transformer->transform(
    data: 123.45,
    arguments: null,
    context: [],
);
echo '# ToString' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToTitleCase
$transformer = new ToTitleCase();
// Foo Bar
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: null,
    context: [],
);
echo '# ToTitleCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToUpperCase
$transformer = new ToUpperCase();
// FOO BAR
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: null,
    context: [],
);
echo '# ToUpperCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Trim
$transformer = new Trim();
// " #[ Foo ]"
$result = $transformer->transform(
    data: ' #[ Foo ] #',
    arguments: new ArgumentIterator([
        new Argument(
            value: ' #',
            key: Trim::ARGUMENT_INDEX_CHARACTERS,
        ),
        new Argument(
            value: 'end',
            key: Trim::ARGUMENT_INDEX_POSITION,
        ),
    ]),
    context: [],
);
echo '# Trim' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Unique
$transformer = new Unique();
//  [
//      123,
//      "foo",
//      "123",
//      123.0,
//      "Foo",
//  ]
$result = $transformer->transform(
    data: [
        123,
        'foo',
        '123',
        123.0,
        '123',
        'foo',
        'Foo',
    ],
    arguments: new ArgumentIterator([
        new Argument(
            value: true,
            key: Unique::ARGUMENT_INDEX_STRICT,
        ),
    ]),
    context: [],
);
echo '# Unique' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ValueMap
$transformer = new ValueMap();
$result = $transformer->transform(
    data: [
        'foo',
        'Foo',
        'bar',
        123.45,
    ],
    arguments: new ArgumentIterator([
        new Argument(
            value: new ArgumentIterator([
                new Argument(
                    value: 'FOO',
                    key: 'foo',
                ),
                new Argument(
                    value: '__bar__',
                    key: 'bar',
                ),
                new Argument(
                    value: 'one-two-three-point-four-five',
                    key: '123.45',
                ),
            ]),
            key: ValueMap::ARGUMENT_INDEX_VALUE_MAP,
        ),
        new Argument(
            value: true,
            key: ValueMap::ARGUMENT_INDEX_STRICT,
        ),
        new Argument(
            value: true,
            key: ValueMap::ARGUMENT_INDEX_CASE_SENSITIVE,
        ),
    ]),
    context: [],
);
echo '# ValueMap (simple map)' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

$transformer = new ValueMap();
$result = $transformer->transform(
    data: [
        'foo',
        'Foo',
        'bar',
        123.45,
    ],
    arguments: new ArgumentIterator([
        new Argument(
            value: new ArgumentIterator([
                new Argument(
                    new ArgumentIterator([
                        new Argument(
                            value: 'foo',
                            key: 'sourceValue',
                        ),
                        new Argument(
                            value: 'FOO',
                            key: 'convertedValue',
                        ),
                         new Argument(
                             value: false,
                             key: 'caseSensitive',
                         ),
                    ]),
                ),
                new Argument(
                    new ArgumentIterator([
                        new Argument(
                            value: 'bar',
                            key: 'sourceValue',
                        ),
                        new Argument(
                            value: '__bar__',
                            key: 'convertedValue',
                        ),
                    ]),
                ),
                new Argument(
                    new ArgumentIterator([
                        new Argument(
                            value: '123.45',
                            key: 'sourceValue',
                        ),
                        new Argument(
                            value: 'one-two-three-point-four-five',
                            key: 'convertedValue',
                        ),
                        new Argument(
                            value: false,
                            key: 'strict',
                        ),
                    ]),
                ),
            ]),
            key: ValueMap::ARGUMENT_INDEX_VALUE_MAP,
        ),
        new Argument(
            value: true,
            key: ValueMap::ARGUMENT_INDEX_STRICT,
        ),
         new Argument(
             value: true,
             key: ValueMap::ARGUMENT_INDEX_CASE_SENSITIVE,
         ),
    ]),
    context: [],
);
echo '# ValueMap (complex map)' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;