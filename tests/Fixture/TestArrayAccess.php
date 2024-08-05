<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Fixture;

/**
 * @implements \ArrayAccess<int|string, mixed>
 * @implements \Iterator<int|string, mixed>
 */
class TestArrayAccess implements \Iterator, \Countable, \ArrayAccess
{
    /**
     * @var int
     */
    private int $position = 0;
    /**
     * @var mixed[]
     */
    private array $data = [];
    /**
     * @var array<int|string>
     */
    private array $keys = [];
    /**
     * @var int
     */
    private int $currentAutoIncrement = -1;

    /**
     * @param mixed[] $data
     * @param bool $retainNumericKeys
     */
    public function __construct(
        array $data = [],
        bool $retainNumericKeys = false,
    ) {
        array_walk($data, function ($value, $offset) use ($retainNumericKeys): void {
            if (is_int($offset) && !$retainNumericKeys) {
                $offset = null;
            }
            $this->offsetSet($offset, $value);
        });
    }

    /**
     * @param string|int|null $offset
     * @param mixed $value
     * @return void
     */
    public function offsetSet(mixed $offset, mixed $value): void
    {
        if (null === $offset) {
            $offset = ++$this->currentAutoIncrement;
        } elseif (is_integer($offset) || ctype_digit($offset)) {
            $offset = (int)$offset;

            $maxNumericKey = $this->getMaxNumericKey();
            $this->currentAutoIncrement = max(
                $offset,
                (int)$maxNumericKey,
            );
        }

        $this->data[$offset] = $value;
        if (!in_array($offset, $this->keys, true)) {
            $this->keys[] = $offset;
        }
    }

    /**
     * @param string|int $offset
     * @return mixed
     */
    public function offsetGet(mixed $offset): mixed
    {
        return $this->data[$offset] ?? null;
    }

    /**
     * @param string|int $offset
     * @return void
     */
    public function offsetUnset(mixed $offset): void
    {
        if (array_key_exists($offset, $this->data)) {
            unset($this->data[$offset]);
            $this->keys = array_keys($this->data);

            $maxNumericKey = $this->getMaxNumericKey();
            $this->currentAutoIncrement = (null === $maxNumericKey)
                ? -1
                : $maxNumericKey;
        }
    }

    /**
     * @param string|int $offset
     * @return bool
     */
    public function offsetExists(mixed $offset): bool
    {
        return array_key_exists($offset, $this->data);
    }

    /**
     * @return void
     */
    public function next(): void
    {
        ++$this->position;
    }

    /**
     * @return string|int|null
     */
    public function key(): string|int|null
    {
        return $this->keys[$this->position] ?? null;
    }

    /**
     * @return bool
     */
    public function valid(): bool
    {
        $key = $this->key();

        return null !== $key
            && array_key_exists($key, $this->data);
    }

    /**
     * @return void
     */
    public function rewind(): void
    {
        $this->position = 0;
    }

    /**
     * @return mixed
     */
    public function current(): mixed
    {
        return $this->data[$this->key()];
    }

    /**
     * @return int
     */
    public function count(): int
    {
        return count($this->data);
    }

    /**
     * @return mixed[]
     */
    public function toArray(): array
    {
        return array_combine(
            keys: $this->keys,
            values: $this->data,
        );
    }

    /**
     * @return int|null
     */
    private function getMaxNumericKey(): ?int
    {
        $numericKeys = array_filter(
            array: $this->keys,
            callback: static fn (string|int $key): bool => is_int($key),
        );

        return $numericKeys
            ? max($numericKeys)
            : null;
    }
}
