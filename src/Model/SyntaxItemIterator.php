<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

/**
 * @property SyntaxItem[] $data
 * @method SyntaxItem[] toArray()
 */
class SyntaxItemIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param SyntaxItem[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param SyntaxItem $item
     * @return void
     */
    public function addItem(SyntaxItem $item): void
    {
        $this->data[] = $item;
    }

    /**
     * @return SyntaxItem
     */
    public function current(): SyntaxItem
    {
        return $this->data[$this->key()];
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return ($this->data[$this->key()] ?? null) instanceof SyntaxItem;
    }
}
