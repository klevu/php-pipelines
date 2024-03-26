<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class SyntaxItem
{
    /**
     * @param string $command
     * @param ArgumentIterator|null $arguments
     */
    public function __construct(
        public readonly string $command,
        public readonly ?ArgumentIterator $arguments = null,
    ) {
    }
}
