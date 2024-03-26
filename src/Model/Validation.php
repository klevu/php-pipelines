<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class Validation
{
    /**
     * @param string $validatorName
     * @param ArgumentIterator|null $arguments
     */
    public function __construct(
        public readonly string $validatorName,
        public readonly ?ArgumentIterator $arguments = null,
    ) {
    }
}
