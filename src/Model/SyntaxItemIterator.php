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
     * @return SyntaxItem|false
     */
    public function current(): SyntaxItem|false
    {
        return $this->data[$this->key()] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof SyntaxItem;
    }
}
