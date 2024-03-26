<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

/**
 * @property Transformation[] $data
 * @method Transformation[] toArray()
 */
class TransformationIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param Transformation[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param Transformation $item
     * @return void
     */
    public function addItem(Transformation $item): void
    {
        $this->data[] = $item;
    }

    /**
     * @return Transformation
     */
    public function current(): Transformation
    {
        return $this->data[$this->key()];
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return ($this->data[$this->key()] ?? null) instanceof Transformation;
    }
}
