<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model\Transformation;

use Klevu\Pipelines\Model\Comparators;

class FilterComparison
{
    /**
     * @param mixed $sourceValue
     * @param Comparators $comparator
     * @param mixed $compareValue
     * @param bool $strict
     */
    public function __construct(
        public readonly mixed $sourceValue,
        public readonly Comparators $comparator,
        public readonly mixed $compareValue,
        public readonly bool $strict = false,
    ) {
    }
}
