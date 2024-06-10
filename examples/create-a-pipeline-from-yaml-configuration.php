<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

class Provider
{
    private int $minOrdersToReturn = 1;
    private int $maxOrdersToReturn = 10;
    private int $minOrderItemsToReturn = 1;
    private int $maxOrderItemsToReturn = 10;

    public function execute(): \Generator
    {
        for ($i = 1; $i <= rand($this->minOrdersToReturn, $this->maxOrdersToReturn); $i++) {
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
                array_fill(0, rand($this->minOrderItemsToReturn, $this->maxOrderItemsToReturn), null),
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

$configurationFilepath = __DIR__ . '/iterate-records.yml';
$pipelineBuilder = new \Klevu\Pipelines\Pipeline\PipelineBuilder();
try {
    $pipeline = $pipelineBuilder->buildFromFile($configurationFilepath);
} catch (Exception $exception) {
    echo $exception::class . PHP_EOL;
    echo $exception->getMessage() . PHP_EOL;
    if (method_exists($exception, 'getErrors')) {
        print_r($exception->getErrors());
    }
    if (method_exists($exception, 'getArguments')) {
        print_r($exception->getArguments());
    }
    die();
}


// EXECUTION
$provider = new Provider();
try {
    $result = $pipeline->execute(
        payload: $provider->execute(),
        context: $context,
    );

    var_dump($result);
} catch (\Klevu\Pipelines\Exception\ValidationException $e) {
    echo 'Validation Failed: ' . $e->getMessage() . PHP_EOL;
    print_r($e->getErrors());
    var_dump($e->getData());
} catch (Exception $exception) {
    echo $exception::class . PHP_EOL;
    echo $exception->getMessage() . PHP_EOL;
    if (method_exists($exception, 'getErrors')) {
        print_r($exception->getErrors());
    }
    if (method_exists($exception, 'getArguments')) {
        print_r($exception->getArguments());
    }
    echo $exception->getTraceAsString();
}
