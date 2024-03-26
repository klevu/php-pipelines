<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class Argument
{
    /**
     * @var mixed
     */
    private mixed $value;
    /**
     * @var mixed
     */
    private mixed $key;

    /**
     * @param mixed $value
     * @param mixed|null $key
     */
    public function __construct(
        mixed $value,
        mixed $key = null,
    ) {
        $this->value = $value;
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue(): mixed
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     * @return void
     */
    public function setValue(mixed $value): void
    {
        $this->value = $value;
    }

    /**
     * @return mixed
     */
    public function getKey(): mixed
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     * @return void
     */
    public function setKey(mixed $key): void
    {
        $this->key = $key;
    }
}
