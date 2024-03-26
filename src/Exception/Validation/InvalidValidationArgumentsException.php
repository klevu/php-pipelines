<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Validation;

use Klevu\Pipelines\Exception\ValidationException;

class InvalidValidationArgumentsException extends ValidationException
{
    /**
     * @var string
     */
    protected string $defaultMessage = 'Invalid arguments for validation';
}
