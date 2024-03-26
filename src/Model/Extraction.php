<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

class Extraction
{
    /**
     * @param string|Extraction|null $accessor
     * @param TransformationIterator|null $transformations
     */
    public function __construct(
        public readonly string|Extraction|null $accessor,
        public readonly ?TransformationIterator $transformations = null,
    ) {
    }
}
