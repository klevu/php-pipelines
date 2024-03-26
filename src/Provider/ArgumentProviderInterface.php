<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider;

use Klevu\Pipelines\Model\ArgumentIterator;

interface ArgumentProviderInterface
{
    /**
     * @param ArgumentIterator|null $arguments
     * @param string|int $argumentKey
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getArgumentValue(
        ?ArgumentIterator $arguments,
        string|int $argumentKey,
        mixed $defaultValue = null,
    ): mixed;

    /**
     * @param ArgumentIterator|null $arguments
     * @param string|int $argumentKey
     * @param mixed|null $defaultValue
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return mixed
     */
    public function getArgumentValueWithExtractionExpansion(
        ?ArgumentIterator $arguments,
        string|int $argumentKey,
        mixed $defaultValue = null,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): mixed;
}
