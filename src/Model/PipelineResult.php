<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class PipelineResult
{
    /**
     * @param bool $success
     * @param mixed|null $payload
     * @param string[] $messages
     */
    public function __construct(
        public readonly bool $success,
        public readonly mixed $payload = null,
        public readonly array $messages = [],
    ) {
    }
}
