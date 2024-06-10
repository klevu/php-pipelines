<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

require_once __DIR__ . '/../vendor/autoload.php';

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\Stage;
use Klevu\Pipelines\Validator\MatchesRegex;

$data = [
    'foo' => ' bar ',
];
$context = [
    'config' => (object)[
        'prependString' => 'Foo',
    ],
];

$pipeline = new Pipeline();
$pipeline->addStage(
    stage: new Stage\Extract(
        extraction: new Extraction(accessor: 'foo'),
    ),
    identifier: 'extract',
);
// Stages can be grouped together within a parent pipeline...
$pipeline->addStage(
    stage: new Pipeline(
        stages: [
            new Stage\Transform(
                transformation: new Transformation(transformerName: 'Trim'),
            ),
            new Stage\Transform(
                transformation: new Transformation(
                    transformerName: 'Prepend',
                    arguments: new ArgumentIterator([
                        new Argument(
                            value: new Extraction(accessor: 'config::prependString'),
                        ),
                        new Argument(' - '),
                    ]),
                ),
            ),
        ],
    ),
    identifier: 'transform',
);
// ... or added individually
$pipeline->addStage(
    stage: new Stage\Validate(
        validation: new Validation(validatorName: 'IsNotEmpty'),
    ),
);
$pipeline->addStage(
    stage: new Stage\Validate(
        validation: new Validation(
            validatorName: 'MatchesRegex',
            arguments: new ArgumentIterator([
                new Argument(
                    value: '/^Foo -.*[^ ]$/',
                    key: MatchesRegex::ARGUMENT_INDEX_REGULAR_EXPRESSION,
                ),
            ]),
        ),
    ),
);

$result = $pipeline->execute(
    payload: $data,
    context: $context,
);
// string(13) "Foo - bar"
var_dump($result);
