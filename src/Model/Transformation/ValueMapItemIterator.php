<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation;

use Klevu\Pipelines\Model\GenericIteratorTrait;
use Klevu\Pipelines\Model\IteratorInterface;

/**
 * @property ValueMapItem[] $data
 * @method ValueMapItem[] toArray()
 */
class ValueMapItemIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param ValueMapItem[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param ValueMapItem $item
     * @return void
     */
    public function addItem(ValueMapItem $item): void
    {
        $this->data[] = $item;
    }

    /**
     * @return ValueMapItem|false
     */
    public function current(): ValueMapItem|false
    {
        return $this->data[$this->key()] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof ValueMapItem;
    }
}
