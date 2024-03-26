<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class ArgumentIteratorFactory
{
    /**
     * @param mixed[] $arguments
     * @return ArgumentIterator
     */
    public function create(array $arguments): ArgumentIterator
    {
        return new ArgumentIterator(
            array_map(
                fn (mixed $argument, string|int $key): Argument => match (true) {
                    is_array($argument) => new Argument(
                        value: $this->create($argument),
                        key: $key,
                    ),
                    $argument instanceof Argument => $argument,
                    default => new Argument(
                        value: $argument,
                        key: $key,
                    ),
                },
                array_values($arguments),
                array_keys($arguments),
            ),
        );
    }
}
