<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\Stage;
use Klevu\Pipelines\Transformer\FormatNumber;
use Klevu\Pipelines\Transformer\MapProperty;
use Klevu\Pipelines\Validator\MatchesRegex;

$data = [
    (object)[
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
    (object)[
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

$pipeline = new Pipeline();

// The source data contains multiple items which we want to process sequentially,
//  so we create an Iterate pipeline
// We set an itemContextKey so we can always refer to the parent item
$iterateRecordsPipeline = new Pipeline\Iterate(
    itemContextKey: 'record',
);
// While we're here, add the iterateRecords pipeline to the parent
// We can continue affecting it after assignment as it is defined as a variable
$pipeline->addStage($iterateRecordsPipeline);

// For each item, we want to create a record containing multiple values,
//  using extraction, transformation, and validation
// For this, we need a CreateRecord pipeline
$createRecordPipeline = new Pipeline\CreateRecord(
    returnObject: true, // By default, the result will be an array; this returns to a \stdClass
);
$iterateRecordsPipeline->addStage($createRecordPipeline);

// Now let's add some extraction, transformation, and validation to the CreateRecord pipeline
// Each stage will add the result to the returned object, using the stage identifier as the key
$idPipeline = new Pipeline(
    stages: [
        'extract' => new Stage\Extract(
            extraction: new Extraction('id'),
        ),
        'transform' => new Stage\Transform(
            transformation: new Transformation('ToString'),
        ),
        'validate' => new Stage\Validate(
            validation: new Validation('IsNotEmpty'),
        ),
    ],
);
$namePipeline = new Pipeline(
    stages: [
        'extract' => new Pipeline\Fallback(
            stages: [
                'name' => new Pipeline(
                    stages: [
                        new Stage\Extract(
                            extraction: new Extraction('name'),
                        ),
                        new Stage\Transform(
                            transformation: new Transformation('Trim'),
                        ),
                        // Validation failure in a fallback pipeline will cause the next stage to run
                        // Otherwise, this value will be used
                        new Stage\Validate(
                            validation: new Validation('IsNotEmpty'),
                        ),
                    ],
                ),
                'parent_name' => new Stage\Extract(
                    extraction: new Extraction('parent_name'),
                ),
            ],
        ),
        // We want multiple transformations so let's group them together
        // It would also be valid to add each of these separately to the namePipeline directly
        'transform' => new Pipeline(
            stages: [
                new Stage\Transform(
                    transformation: new Transformation('Trim'),
                ),
                new Stage\Transform(
                    transformation: new Transformation(
                        transformerName: 'Prepend',
                        arguments: new ArgumentIterator([
                            new Argument('Name: '),
                        ]),
                    ),
                ),
            ],
        ),
        'validate' => new Stage\Validate(
            validation: new Validation('IsNotEmpty'),
        ),
    ],
);
$pricePipeline = new Pipeline(
    stages: [
        'extract' => new Stage\Extract(
            extraction: new Extraction('price'),
        ),
        // Validation does not have to go at the end - here we are checking the incoming
        //  data before then proceeding with transformation and subsequent validation
        'prevalidate' => new Stage\Validate(
            validation: new Validation('IsPositiveNumber'),
        ),
        'formatNumber' => new Stage\Transform(
            transformation: new Transformation(
                transformerName: 'FormatNumber',
                arguments: new ArgumentIterator([
                    new Argument(
                        value: 2,
                        key: FormatNumber::ARGUMENT_INDEX_DECIMALS,
                    ),
                    new Argument(
                        value: '',
                        key: FormatNumber::ARGUMENT_INDEX_THOUSANDS_SEPARATOR,
                    ),
                ]),
            ),
        ),
        'addCurrencyCode' => new Stage\Transform(
            transformation: new Transformation(
                transformerName: 'Prepend',
                arguments: new ArgumentIterator([
                    new Argument(new Extraction('currency::code')),
                    new Argument(' '),
                ]),
            ),
        ),
        'postvalidate' => new Stage\Validate(
            validation: new Validation(
                validatorName: 'MatchesRegex',
                arguments: new ArgumentIterator([
                    new Argument(
                        value: '/^[A-Z]{3} [\d]+\.\d{2}$/',
                        key: MatchesRegex::ARGUMENT_INDEX_REGULAR_EXPRESSION,
                    ),
                ]),
            ),
        ),
    ],
);
$emailPipeline = new Pipeline(
    stages: [
        new Stage\Extract(
            extraction: new Extraction('customer.email'),
        ),
        new Stage\Validate(
            validation: new Validation('IsEmail'),
        ),
    ],
);
$highValueItemsPipeline = new Pipeline(
    stages: [
        'extract' => new Stage\Extract(
            extraction: new Extraction('items'),
        ),
        'filter' => new Stage\Transform(
            transformation: new Transformation(
                transformerName: 'FilterCompare',
                arguments: new ArgumentIterator([
                    new Argument(
                        new ArgumentIterator([
                            new Argument(new Extraction('value')),
                            new Argument(Comparators::GREATER_THAN_OR_EQUALS),
                            new Argument(new Extraction('config::items.high_value_threshold')),
                        ]),
                    ),
                ]),
            ),
        ),
        'count' => new Stage\Transform(
            transformation: new Transformation(
                transformerName: 'Count',
            ),
        ),
    ],
);
$tagsPipeline = new Pipeline(
    stages: [
        new Stage\Extract(
            extraction: new Extraction('items'),
        ),
        new Stage\Transform(
            transformation: new Transformation(
                transformerName: 'MapProperty',
                arguments: new ArgumentIterator([
                    new Argument(
                        value: 'tags',
                        key: MapProperty::ARGUMENT_INDEX_ACCESSOR
                    ),
                ]),
            ),
        ),
        new Stage\Transform(
            transformation: new Transformation('Merge'),
        ),
        new Stage\Transform(
            transformation: new Transformation('Trim'),
        ),
        new Stage\Transform(
            transformation: new Transformation('Unique'),
        ),
    ],
);

// Add the child pipelines to CreateRecord
$createRecordPipeline->addStage($idPipeline, 'id');
$createRecordPipeline->addStage($namePipeline, 'name');
$createRecordPipeline->addStage($pricePipeline, 'price');
$createRecordPipeline->addStage($emailPipeline, 'email');
$createRecordPipeline->addStage($highValueItemsPipeline, 'high_value_items_count');
$createRecordPipeline->addStage($tagsPipeline, 'tags');

// Let's also create a small custom stage
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
// Add this to the parent pipeline, so it shows us the result of the entire process
$pipeline->addStage(new OutputAsJson());

// Execute the pipeline
try {
    $result = $pipeline->execute(
        payload: $data,
        context: $context,
    );
} catch (ExtractionException|TransformationException|ValidationException $exception) {
    echo 'Data is not valid: ' . $exception->getMessage() . PHP_EOL;
    if (method_exists($exception, 'getErrors')) {
        print_r($exception->getErrors());
    }
}
