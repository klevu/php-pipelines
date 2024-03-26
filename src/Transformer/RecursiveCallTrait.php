<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;

/**
 * @method mixed transform(mixed $data, ?ArgumentIterator $arguments = null, ?\ArrayAccess $context = null)
 * @see TransformerInterface
 */
trait RecursiveCallTrait
{
    use ConvertIterableToArrayTrait;

    /**
     * @var bool
     */
    private bool $isRecursiveCall = false;

    /**
     * @param mixed $data
     * @return bool
     */
    private function shouldCallRecursively(mixed $data): bool
    {
        return !$this->isRecursiveCall && is_iterable($data);
    }

    /**
     * @param mixed[] $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     */
    private function performRecursiveCall(
        iterable $data,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): mixed {
        if (!($this instanceof TransformerInterface)) {
            throw new \LogicException(sprintf(
                'Class %s must implement %s to handle recursive transform calls',
                $this::class,
                TransformerInterface::class,
            ));
        }

        $this->isRecursiveCall = true;
        $return = array_map(
            fn (mixed $item): mixed => $this->transform(
                data: $item,
                arguments: $arguments,
                context: $context,
            ),
            $this->convertIterableToArray($data),
        );
        $this->isRecursiveCall = false;

        return $return;
    }
}
