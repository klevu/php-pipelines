<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/** @noinspection PhpRedundantOptionalArgumentInspection */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Model\TransformationIteratorFactory;
use Klevu\Pipelines\ObjectManager\TransformerManager;
use Klevu\Pipelines\Transformer\TransformerInterface;

$transformationIteratorFactory = new TransformationIteratorFactory();
$transformerManager = new TransformerManager();

## Append
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Append(" - ", $config::bar)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// "foo - baz"
$result = $transformer->transform(
    data: 'foo',
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ChangeCase("uppercase")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// FOO BAR
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: $transformation->arguments,
    context: [],
);
echo '# ChangeCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## EscapeHtml
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('EscapeHtml(null, null, false)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// &quot;&lt;strong&gt;&quot;strong &amp; test&quot;&lt;/strong&gt;&quot;;
$result = $transformer->transform(
    data: '"<strong>&quot;strong & test&quot;</strong>";',
    arguments: $transformation->arguments,
    context: [],
);
echo '# EscapeHtml' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## FilterCompare
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('FilterCompare([$, "gt", $config::threshold])')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('FirstItem')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
    context: [],
);
echo '# FilterCompare' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## FormatNumber
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('FormatNumber(2, $config::decimal_separator, "-")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// 12_345::68
$result = $transformer->transform(
    data: 12345.67890,
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Join($config::separator)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// Foo:::Bar:::Baz
$result = $transformer->transform(
    data: [
        'Foo',
        'Bar',
        'Baz',
    ],
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('MapProperty("value")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
    context: [],
);
echo '# MapProperty' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Max
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Max')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
    context: [],
);
echo '# Max' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## MaxWords
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('MaxWords($config::max_words, "[...]")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// 10025
$result = $transformer->transform(
    data: 'This is a string containing ten words as an example',
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Min')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
    context: [],
);
echo '# Min' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Prepend
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Prepend(" - ", $config::bar)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// " - bazfoo"
$result = $transformer->transform(
    data: [
        99,
        42,
        3.14,
        -17,
        10025,
        9.99,
    ],
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Split($config::separator)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

//  [
//      "Parts",
//      "of;a",
//      "string;:;to split",
//      "into parts",
//      "",
//  ]
$result = $transformer->transform(
    data: 'Parts;;of;a;;string;:;to split;;into parts;;',
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('StripTags(["p"], ["script"])')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// <p>This is some text. It contains a nasty  script tag.</p>
$result = $transformer->transform(
    data: '<p>This is some <strong>text</strong>. It contains a nasty <script>alert("Bad things");</script> script tag.</p>',
    arguments: $transformation->arguments,
    context: [],
);
echo '# StripTags' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToDateString
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ToDateString($config::date_format, "Europe/London")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// Wednesday, 17th May at 7:45 pm
$result = $transformer->transform(
    data: '2023-05-17T18:45+00:00',
    arguments: $transformation->arguments,
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
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ToLowerCase')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// foo bar
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: $transformation->arguments,
    context: [],
);
echo '# ToLowerCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToString
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ToString')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// "123.45"
$result = $transformer->transform(
    data: 123.45,
    arguments: $transformation->arguments,
    context: [],
);
echo '# ToString' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToTitleCase
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ToTitleCase')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// Foo Bar
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: $transformation->arguments,
    context: [],
);
echo '# ToTitleCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ToUpperCase
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('ToUpperCase')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// FOO BAR
$result = $transformer->transform(
    data: 'fOo Bar',
    arguments: $transformation->arguments,
    context: [],
);
echo '# ToUpperCase' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Trim
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Trim(" #", "end")')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

// " #[ Foo ]"
$result = $transformer->transform(
    data: ' #[ Foo ] #',
    arguments: $transformation->arguments,
    context: [],
);
echo '# Trim' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## Unique
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration('Unique(true)')
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

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
    arguments: $transformation->arguments,
    context: [],
);
echo '# Unique' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

## ValueMap
$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration(
        <<<'SYNTAX'
        ValueMap({"foo": "FOO", "bar": "__bar__", "123.45": "one-two-three-point-four-five"}, true, true)
        SYNTAX
    )
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

$result = $transformer->transform(
    data: [
        'foo',
        'Foo',
        'bar',
        123.45,
    ],
    arguments: $transformation->arguments,
    context: [],
);
echo '# ValueMap (simple map)' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

$transformation = $transformationIteratorFactory
    ->createFromSyntaxDeclaration(
        <<<'SYNTAX'
        ValueMap([{"sourceValue":"foo", "convertedValue":"FOO", "caseSensitive": false}, {"sourceValue": "bar", "convertedValue": "__bar__"}, {"sourceValue": "123.45", "convertedValue": "one-two-three-point-four-five", "strict": true}], true, true)
        SYNTAX
    )
    ->current();
/** @var TransformerInterface $transformer */
$transformer = $transformerManager->get($transformation->transformerName);

$result = $transformer->transform(
    data: [
        'foo',
        'Foo',
        'bar',
        123.45,
    ],
    arguments: $transformation->arguments,
    context: [],
);
echo '# ValueMap (complex map)' . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;