<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Extractor\Extractor;

class SourceObject
{
    /**
     * @param mixed[] $foo
     * @param mixed|null $publicProperty
     * @param mixed|null $privateProperty
     */
    public function __construct(
        public readonly array $foo = ['bar' => 'baz'],
        public readonly mixed $publicProperty = null,
        private readonly mixed $privateProperty = null,
    ) {
    }

    /**
     * @return mixed[]
     */
    public function getFoo(): array
    {
        return $this->foo;
    }

    /**
     * @return mixed
     */
    public function getPrivateProperty(): mixed
    {
        return $this->privateProperty;
    }
}

$extractor = new Extractor();

// Object extraction
$sourceObject = new SourceObject();
$result = [
    $extractor->extract($sourceObject, 'foo'), // ['bar' => 'baz']
    $extractor->extract($sourceObject, 'foo.bar'), // 'baz'
    $extractor->extract($sourceObject, 'getFoo()'), // ['bar' => 'baz']
    $extractor->extract($sourceObject, 'getFoo().bar'), // ['bar' => 'baz']
];
echo "Object Extraction" . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

// Array extraction
$sourceArray = [
    'foo' => [
        'bar' => 'baz',
    ],
    'sourceObject' => $sourceObject,
];
$result = [
    $extractor->extract($sourceArray, 'foo'), // ['bar' => 'baz']
    $extractor->extract($sourceArray, 'foo.bar'), // 'baz'
    $extractor->extract($sourceArray, 'sourceObject'), // <SourceObject>
    $extractor->extract($sourceArray, 'sourceObject.getFoo().bar'), // 'baz'
];
echo "Array Extraction" . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

// Special cases: loops
$sourceIterableProperty = [
    new SourceObject(publicProperty: 'foo'),
    new SourceObject(publicProperty: 'bar'),
    new SourceObject(publicProperty: 'baz'),
];
echo "Source Iterable Property" . PHP_EOL;
try {
    $extractor->extract($sourceIterableProperty, 'publicProperty'); // Exception
} catch (ExtractionException $e) {
    echo "Encountered Exception: " . $e->getMessage() . PHP_EOL;
}
echo PHP_EOL . "---" . PHP_EOL;

$sourceIterableMethod = [
    new SourceObject(privateProperty: 'foo'),
    new SourceObject(privateProperty: 'bar'),
    new SourceObject(privateProperty: 'baz'),
];
$result = $extractor->extract($sourceIterableMethod, 'getPrivateProperty()'); // ['foo', 'bar', 'baz']
echo "Source Iterable Property" . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;

// Context
$context = [
    'fromArray' => [
        'foo' => 'bar',
    ],
    'fromObject' => new SourceObject(),
];
$result = [
    $extractor->extract($sourceObject, 'fromArray::foo', $context), // 'bar'
    $extractor->extract($sourceObject, 'fromObject::getFoo().bar', $context), // 'baz'
];
echo "Using Context" . PHP_EOL;
var_dump($result);
echo PHP_EOL . "---" . PHP_EOL;
