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
use Klevu\Pipelines\Model\SyntaxItem;
use Klevu\Pipelines\Model\SyntaxItemIterator;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\Parser\SyntaxParser;

$syntaxParser = new SyntaxParser();

# NULL
//$syntax = null;
//printSyntaxOutput(
//    title: 'NULL',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);
//
//# No arguments
//$syntax = 'Transform';
//printSyntaxOutput(
//    title: 'No arguments',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);
//
//# Simple arguments
//$syntax = 'Transform("foo", 0, null, false, $foo,)'; // Note: trailing comma evaluates a null argument
//printSyntaxOutput(
//    title: 'Simple arguments',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);

# Inline transformations
//$syntax = 'Transform($bar|NumberFormat(0), $foo|ToString|Split(", ")|Unique|Join(","))';
////$syntax = 'Transform($bar|NumberFormat(0),)'; // Edge case bug
//$syntax = 'Transform($bar|NumberFormat(0))';
//$syntax = 'Transform($bar()|NumberFormat(0), $foo|Trim|NumberFormat(, ",", ":"))';
//$syntax = 'Transform($bar()|Trim)';
////$syntax = 'Transform($foo|Trim|NumberFormat(0,,$config::foo|A(["b", $c])))';
$syntax = "Transform|Transform";
$syntax = "Transform(Transform())";

$debug = [];
try {
    printSyntaxOutput(
        title: 'Inline Transformations',
        syntax: $syntax,
        syntaxItems: $syntaxParser->parse($syntax, debug: $debug),
    );
} catch (\Exception $e) {
    echo $syntax . PHP_EOL;
    echo $e::class . ': ' . $e->getMessage() . PHP_EOL;
}
echo "DEBUG" . PHP_EOL;
echo json_encode($debug, JSON_PRETTY_PRINT);

//
//# Simple arguments in array
//$syntax = 'Transform(["foo", 0, null, false, $foo])';
//printSyntaxOutput(
//    title: 'Simple arguments in array',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);
//
//# Nested arrays
//$syntax = 'Transform("foo", ["bar", "baz"], [["wom"], ["bat"]])';
//printSyntaxOutput(
//    title: 'Nested Arrays',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);

//# Objects
//$syntax = 'Transform(["foo", {"bar": "baz"}], {"foo": {"bar": "baz"}}, {"wom": [$bat]})';
//printSyntaxOutput(
//    title: 'Objects',
//    syntax: $syntax,
//    syntaxItems: $syntaxParser->parse($syntax),
//);

//# Multiple Syntax Items

function printSyntaxOutput(
    string $title,
    ?string $syntax,
    SyntaxItemIterator $syntaxItems,
): void {
    $output = sprintf('# %s', $title) . PHP_EOL;
    $output .= sprintf('Input: %s', var_export($syntax, true)) . PHP_EOL;
    $output .= sprintf('Found %d syntax item(s)', $syntaxItems->count()) . PHP_EOL;

    /** @var SyntaxItem $syntaxItem */
    foreach ($syntaxItems as $syntaxItem) {
        $output .= sprintf('  * Command: %s', $syntaxItem->command) . PHP_EOL;
        $output .= '    ' . getArgumentsOutput($syntaxItem->arguments, 4);
    }
    $output .= PHP_EOL . ' --- ' . PHP_EOL . PHP_EOL;

    echo $output;
}


function getArgumentsOutput(
    ?ArgumentIterator $arguments,
    int $indent = 0,
): ?string {
    $output = sprintf(
            'Arguments (%d):',
            $arguments?->count(),
        ) . PHP_EOL;
    /** @var Argument $argument */
    foreach ($arguments ?? [] as $argument) {
        $output .= sprintf(
                '%s- Key: %s',
                str_repeat(' ', $indent),
                match (true) {
                    is_scalar($argument->key) => var_export($argument->key, true),
                    $argument->key instanceof Extraction => getExtractionOutput($argument->key, $indent + 9),
                    default => get_debug_type($argument->key),
                },
            ) . PHP_EOL;
        $output .= sprintf(
                '%s  Value: %s',
                str_repeat(' ', $indent),
                match (true) {
                    is_scalar($argument->value) => var_export($argument->value, true),
                    $argument->value instanceof Extraction => getExtractionOutput($argument->value, $indent + 9),
                    $argument->value instanceof ArgumentIterator => getArgumentsOutput($argument->value, $indent + 10),
                    default => get_debug_type($argument->value),
                },
            ) . PHP_EOL;
    }

    return $output;
}

function getExtractionOutput(
    Extraction $extraction,
    int $indent = 0,
): string {
    $output = sprintf(
            'Extraction',
        ) . PHP_EOL;
    $output .= sprintf(
            '%s- Accessor: %s',
            str_repeat(' ', $indent),
            $extraction->accessor,
        ) . PHP_EOL;
    $output .= sprintf(
            '%s- Transformations (%d): %s',
            str_repeat(' ', $indent),
            $extraction->transformations?->count(),
            getTransformationsOutput($extraction->transformations, $indent + 2),
        ) . PHP_EOL;

    return $output;
}

function getTransformationsOutput(
    ?TransformationIterator $transformations,
    int $indent = 0,
): string {
    if (!$transformations?->count()) {
        return '';
    }

    $output = PHP_EOL;
    foreach ($transformations as $transformation) {
        $output .= sprintf(
            '%s- Transformer: %s',
            str_repeat(' ', $indent),
            $transformation->transformerName,
        ) . PHP_EOL;
        $output .= sprintf(
            '%s  %s',
            str_repeat(' ', $indent),
            getArgumentsOutput($transformation->arguments, $indent + 2)
        );
    }

    return $output;
}