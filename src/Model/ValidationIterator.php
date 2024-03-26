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
     * @return Validation
     */
    public function current(): Validation
    {
        return $this->data[$this->key()];
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return ($this->data[$this->key()] ?? null) instanceof Validation;
    }
}
