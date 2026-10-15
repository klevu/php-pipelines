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
     * @return Transformation|false
     */
    public function current(): Transformation|false
    {
        $key = $this->key();
        if (null === $key) {
            return false;
        }

        return $this->data[$key] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof Transformation;
    }
}
