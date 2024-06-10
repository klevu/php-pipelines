<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

// See 1-2.create-a-complex-pipeline.php for comparison

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\PipelineException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\ObjectManager\PipelineFactoryManager;
use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\PipelineBuilder;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\Stage;
use Klevu\Pipelines\Transformer\FormatNumber;
use Klevu\Pipelines\Transformer\MapProperty;
use Klevu\Pipelines\Validator\MatchesRegex;

// Create a provider to demonstrate use of generators. We will use the same data as the previous
//  example for ease of comparison

class Provider
{
    private array $data = [
        [
            'id' => 12345,
            'name' => 'Foo  ',
            'parent_name' => 'Bar',
            'price' => 12345.6789,
            'customer' => [
                'email' => 'contact@klevu.com',
            ],
            'items' => [
                [
                    'value' => 12,
                    'tags' => [
                        '  foo ',
                        ' bar',
                    ],
                ],
                [
                    'value' => 1,
                    'tags' => [
                        'foo',
                    ],
                ],
            ],
        ],
        [
            'id' => 987654,
            'name' => ' ',
            'parent_name' => 'Baz',
            'price' => 0.987654321,
            'customer' => [
                'email' => null,
            ],
            'items' => [
                [
                    'value' => 14,
                    'tags' => [],
                ],
                [
                    'value' => 7,
                    'tags' => [
                        'foo',
                    ],
                ],
            ],
        ],
    ];

    public function getNextRecord(): \Generator
    {
        foreach ($this->data as $record) {
            yield (object)$record;
        }
    }
}

// Create the same custom stage as previously
class OutputAsJson implements PipelineInterface
{
    public function addStage(PipelineInterface $stage, ?string $identifier = null): void
    {
        // Not implemented
    }

    public function execute(mixed $payload, array $context = []): mixed
    {
        echo json_encode($payload, JSON_PRETTY_PRINT);

        return $payload;
    }
}
// As we are using the builder, we also need a factory class
class OutputAsJsonFactory implements \Klevu\Pipelines\Pipeline\PipelineFactoryInterface
{
    public function create(?array $args = null, ?array $stages = null): PipelineInterface
    {
        return new OutputAsJson();
    }
}

// Register our pipeline factory
// In the real world, this should be done using your application's di system
$pipelineFactoryManager = new PipelineFactoryManager(
    sharedInstances: [
        'OutputAsJsonFactory' => new OutputAsJsonFactory(),
    ],
);
$pipelineBuilder = new PipelineBuilder(
    pipelineFactoryManager: $pipelineFactoryManager,
);

// Set up
try {
    $pipeline = $pipelineBuilder->buildFromFile(__DIR__ . '/complex-pipeline.yml');
} catch (PipelineException $exception) {
    echo $exception->getMessage();
    die();
}

// Execute
$provider = new Provider();
$context = [
    'currency' => [
        'code' => 'GBP',
    ],
    'config' => [
        'items' => [
            'high_value_threshold' => 7,
        ],
    ],
];

try {
    $result = $pipeline->execute(
        payload: $provider->getNextRecord(),
        context: $context,
    );
} catch (ExtractionException|TransformationException|ValidationException $exception) {
    echo 'Data is not valid: ' . $exception->getMessage() . PHP_EOL;
    if (method_exists($exception, 'getErrors')) {
        print_r($exception->getErrors());
    }
}