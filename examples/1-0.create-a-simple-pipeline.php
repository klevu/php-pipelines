<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\PipelineInterface;

class JsonEncodeStage implements PipelineInterface
{
    /**
     * @param PipelineInterface $stage
     * @param string|null $identifier
     * @return void
     */
    public function addStage(PipelineInterface $stage, ?string $identifier = null): void
    {
        // Not implemented in this example
    }

    /**
     * JSON-encodes the passed payload
     *
     * @param mixed $payload
     * @param mixed[] $context
     * @return mixed
     */
    public function execute(mixed $payload, array $context = []): mixed
    {
        return json_encode($payload);
    }
}

$data = [
    'foo' => 'bar',
];

$pipeline = new Pipeline();
$pipeline->addStage(new JsonEncodeStage());

$result = $pipeline->execute($data);
// string(13) "{"foo":"bar"}"
var_dump($result);
