<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

/**
 * @extends \Iterator<int|string, mixed>
 */
interface IteratorInterface extends \Iterator, \Countable
{
    /**
     * @return object[]
     */
    public function toArray(): array;

    /**
     * @param callable $callback
     * @param int $mode
     * @return self
     */
    public function filter(callable $callback, int $mode = 0): self;

    /**
     * @param callable $callback
     * @return self
     */
    public function walk(callable $callback): self;

    /**
     * @param IteratorInterface $iterator
     * @return self
     */
    public function merge(IteratorInterface $iterator): self;
}
