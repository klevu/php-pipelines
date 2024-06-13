<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Model\ArgumentIterator;

/**
 * Transformer to append one or more scalar values to end of input data string
 * Arguments
 *  ...- <string> Appended string
 */
class Append extends AbstractConcatenate
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
        $return = $this->argumentIteratorFactory->create([
            (string)$data,
        ]);

        return $return->merge($arguments);
    }
}
