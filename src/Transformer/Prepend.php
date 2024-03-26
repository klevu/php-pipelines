<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;

/**
 * Transformer to prepend one or more scalar values to start of input data string
 * Arguments
 *  ...- <string|Extraction> Prepended string
 */
class Prepend extends AbstractConcatenate
{
    /**
     * @param string|bool|int|float|null $data
     * @param ArgumentIterator $arguments
     * @return ArgumentIterator
     */
    protected function prepareJoinTransformData(
        null|bool|string|int|float $data,
        ArgumentIterator $arguments,
    ): ArgumentIterator {
        return $arguments->merge(
            new ArgumentIterator([
                new Argument((string)$data),
            ]),
        );
    }
}
