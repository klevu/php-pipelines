<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Pipeline\Extract\FallbackExtractPipeline;
use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\Stage\Extract as ExtractStage;
use Klevu\Pipelines\Pipeline\Stage\Transform as TransformStage;
use Klevu\Pipelines\Pipeline\Stage\Validate as ValidateStage;
use Klevu\Pipelines\Transformer\FormatNumber as FormatNumberTransformer;

class Provider
{
    /**
     * @return Generator
     */
    public function execute(): \Generator
    {
        for ($i = 1; $i <= rand(1, 10); $i++) {
            yield $this->generateOrder();
        }
    }

    /**
     * @return mixed[]
     */
    private function generateOrder(): array
    {
        $orderId = rand(1, 999999);

        return [
            'id' => $orderId,
            'order_date' => date('c', rand(1640995200, time())),
            'order_number' => sprintf('KLEVU-%s', $orderId),
            'currency' => array_rand(array_flip(['GBP', 'USD', 'EUR'])),
            'customer' => [
                'name' => array_rand(array_flip(['John', 'Jane', 'Jack']))
                    . ' '
                    . array_rand(array_flip(['Doe', 'Smith', 'Jones'])),
                'email' => 'contact@klevu.com',
            ],
            'meta' => [
                'ip_address' => array_rand(array_flip(['127.0.0.1', '172.0.0.1'])),
            ],
            'items' => array_map(
                [$this, 'generateOrderItem'],
                array_fill(0, rand(1, 10), null),
            ),
        ];
    }

    /**
     * @return mixed[]
     */
    private function generateOrderItem(): array
    {
        return [
            'item_id' => rand(1, 999),
            'product' => array_rand(array_flip(['T-Shirt', 'Shoes', 'Hat']))
                . ' - '
                . array_rand(array_flip(['Black', 'White', 'Green'])),
            'product_id' => rand(1000, 99999),
            'parent_id' => (rand(0, 1))
                ? rand(100, 9999)
                : null,
            'price_incl_tax' => rand(10000, 999999999) / 100000,
            'quantity' => rand(1, 10),
        ];
    }
}

// Any additional contextual data the pipeline may use (eg config settings)
$context = [
    'account' => [
        'js_api_key' => 'klevu-1234567890',
        'rest_api_key' => 'ABCDE1234567890',
    ]
];

$pipeline = new Pipeline\Iterate([
    new ExtractStage(extraction: new Extraction('items')),
    new Pipeline\CreateRecord([
        'items' => new Pipeline\Iterate([
            new Pipeline\CreateRecord([
                'order_id' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('root::order_number')),
                    new TransformStage(transformation: new Transformation('ToString')),
                    new TransformStage(transformation: new Transformation('Trim')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'order_line_id' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('item_id')),
                    new TransformStage(transformation: new Transformation('ToString')),
                    new TransformStage(transformation: new Transformation('Trim')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'item_name' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('product')),
                    new TransformStage(transformation: new Transformation('ToString')),
                    new TransformStage(transformation: new Transformation('Trim')),
                    new TransformStage(transformation: new Transformation('StripTags')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'item_id' => new Pipeline([
                    new Pipeline\Fallback([
                        // With parent
                        new Pipeline([
                            new ExtractStage(extraction: new Extraction('parent_id')),
                            new ValidateStage(validation: new Validation('IsNotEmpty')), // Causes pipeline to fallback
                            new TransformStage(transformation: new Transformation('ToString')),
                            new TransformStage(transformation: new Transformation(
                                transformerName: 'Append',
                                arguments: [
                                    '-',
                                    new Extraction('source::product_id'),
                                ] ,
                            )),
                        ]),
                        // Without parent
                        new Pipeline([
                            new ExtractStage(extraction: new Extraction('product_id')),
                            new TransformStage(transformation: new Transformation('ToString')),
                        ]),
                    ]),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'item_group_id' => new Pipeline([
                    new Pipeline\Fallback([
                        // With parent
                        new Pipeline([
                            new ExtractStage(extraction: new Extraction('parent_id')),
                            new ValidateStage(validation: new Validation('IsNotEmpty')), // Causes pipeline to fallback
                            new TransformStage(transformation: new Transformation('ToString')),
                        ]),
                        // Without parent
                        new Pipeline([
                            new ExtractStage(extraction: new Extraction('product_id')),
                            new TransformStage(transformation: new Transformation('ToString')),
                        ]),
                    ]),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'item_variant_id' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('product_id')),
                    new TransformStage(transformation: new Transformation('ToString')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                ]),
                'unit_price' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('price_incl_tax')),
                    new TransformStage(transformation: new Transformation(
                        transformerName: 'FormatNumber',
                        arguments: [
                            FormatNumberTransformer::ARGUMENT_INDEX_DECIMALS => 2,
                            FormatNumberTransformer::ARGUMENT_INDEX_DECIMAL_SEPARATOR => '.',
                            FormatNumberTransformer::ARGUMENT_INDEX_THOUSANDS_SEPARATOR => '',
                        ],
                    )),
                    new ValidateStage(validation: new Validation('IsPositiveNumber')),
                ]),
                'currency' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('root::currency')),
                    new TransformStage(transformation: new Transformation('ToString')),
                    new TransformStage(transformation: new Transformation('Trim')),
                    new TransformStage(transformation: new Transformation('ToUpperCase')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                    new ValidateStage(validation: new Validation('IsCurrencyCode')),
                ]),
                'units' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('quantity')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                    new ValidateStage(validation: new Validation('IsPositiveNumber')),
                ]),
                'ip_address' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('root::meta.ip_address')),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                    new ValidateStage(validation: new Validation('IsIpAddress')),
                ]),
                'order_date' => new Pipeline([
                    new ExtractStage(extraction: new Extraction('root::order_date')),
                    new TransformStage(transformation: new Transformation(
                        transformerName: 'ToDateString',
                        arguments: [
                            'Y-m-d H:i:s',
                        ],
                    )),
                    new ValidateStage(validation: new Validation('IsNotEmpty')),
                    new ValidateStage(validation: new Validation('IsValidDate')),
                ]),
            ]),
        ]),
    ]),
]);

// EXECUTION
$provider = new Provider();
try {
    $result = $pipeline->execute(
        payload: $provider->execute(),
        context: $context,
    );

    var_dump($result);
} catch (ValidationException $e) {
    echo 'Validation Failed: ' . $e->getMessage() . PHP_EOL;
    print_r($e->getErrors());
}