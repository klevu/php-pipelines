<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Fixture;

class TestObject
{
    /**
     * @param mixed|null $publicProperty
     * @param mixed|null $privateProperty
     */
    public function __construct(
        public readonly mixed $publicProperty = null,
        private readonly mixed $privateProperty = null,
    ) {
    }

    /**
     * @return mixed
     */
    public function getPrivateProperty(): mixed
    {
        return $this->privateProperty;
    }
}
