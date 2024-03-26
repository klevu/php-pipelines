<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Fixture\Validator;

use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Validator\ValidatorInterface;

abstract class AbstractValidator implements ValidatorInterface
{
    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return void
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        return;
    }
}
