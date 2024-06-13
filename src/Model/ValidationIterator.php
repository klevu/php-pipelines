<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

/**
 * @property Validation[] $data
 * @method Validation[] toArray()
 */
class ValidationIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param Validation[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param Validation $item
     * @return void
     */
    public function addItem(Validation $item): void
    {
        $this->data[] = $item;
    }

    /**
     * @return Validation|false
     */
    public function current(): Validation|false
    {
        return $this->data[$this->key()] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof Validation;
    }
}
