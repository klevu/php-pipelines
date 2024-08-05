<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

/**
 * @property Argument[] $data
 * @method Argument[] toArray()
 */
class ArgumentIterator implements IteratorInterface
{
    use GenericIteratorTrait;

    /**
     * @param Argument[] $data
     */
    public function __construct(array $data = [])
    {
        array_walk($data, [$this, 'addItem']);
    }

    /**
     * @param Argument $item
     * @return void
     */
    public function addItem(Argument $item): void
    {
        if (null === $item->getKey()) {
            $item->setKey(count($this->data));
        }

        $this->data[] = $item;
    }

    /**
     * @return Argument|false
     */
    public function current(): Argument|false
    {
        return $this->data[$this->key()] ?? false;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        return $this->current() instanceof Argument;
    }

    /**
     * @param mixed $key
     * @return Argument|null
     */
    public function getByKey(mixed $key): ?Argument
    {
        /** @var ArgumentIterator $matches */
        $matches = $this->filter(
            static fn (Argument $argument): bool => $argument->getKey() === $key,
        );

        return ($matches->count() && $matches->current())
            ? $matches->current()
            : null;
    }

    /**
     * @param IteratorInterface $iterator
     *
     * @return $this
     */
    public function mergeByArgumentKey(IteratorInterface $iterator): self
    {
        $return = clone $this;

        if (!($iterator instanceof ArgumentIterator)) {
            throw new \InvalidArgumentException(
                message: sprintf(
                    'iterator argument must be instance of %s; received %s',
                    ArgumentIterator::class,
                    $iterator::class,
                ),
            );
        }

        /** @var Argument $item */
        foreach ($return as $item) {
            $replacementItem = $iterator->getByKey(
                key: $item->getKey(),
            );
            if (!$replacementItem) {
                continue;
            }

            $item->setValue(
                value: $replacementItem->getValue(),
            );
        }

        return $return;
    }
}
