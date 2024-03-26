<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

trait GenericIteratorTrait
{
    /**
     * @var int
     */
    private int $position = 0;
    /**
     * @var object[]
     */
    private array $data = [];

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return int
     */
    public function key(): int
    {
        return $this->position;
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return object[]
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * @param callable $callback
     * @param int $mode
     * @return self
     */
    public function filter(callable $callback, int $mode = 0): self
    {
        return new self(
            array_filter($this->toArray(), $callback, $mode),
        );
    }

    /**
     * @param callable $callback
     * @param mixed|null $arg
     * @return self
     */
    public function walk(callable $callback, mixed $arg = null): self
    {
        $data = $this->toArray();
        array_walk($data, $callback, $arg);

        return new self($data);
    }

    /**
     * @param IteratorInterface $iterator
     * @return self
     */
    public function merge(IteratorInterface $iterator): self
    {
        if (!method_exists($this, 'addItem')) {
            throw new \LogicException(sprintf(
                'Iterator %s does not contain addItem method; Cannot merge',
                $this::class,
            ));
        }

        foreach ($iterator as $item) {
            $this->addItem($item); // @phpstan-ignore-line
        }

        return new self($this->toArray());
    }
}
