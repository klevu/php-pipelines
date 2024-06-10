<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation;

use Klevu\Pipelines\Model\GenericIteratorTrait;
use Klevu\Pipelines\Model\IteratorInterface;

/**
 * @property FilterComparison[] $data
 * @method FilterComparison[] toArray()
 */
class FilterComparisonIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param FilterComparison[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param FilterComparison $item
     * @return void
     */
    public function addItem(FilterComparison $item): void
    {
        $this->data[] = $item;
    }

    /**
     * @return FilterComparison|false
     */
    public function current(): FilterComparison|false
    {
        return $this->data[$this->key()] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof FilterComparison;
    }
}
