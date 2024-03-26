<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Validation;

use Klevu\Pipelines\Exception\ValidationException;

/**
 * Specific validation exception thrown when the data sent to test is of an incorrect type, meaning no contextual
 *  checks can be performed
 */
class InvalidTypeValidationException extends ValidationException
{
    /**
     * @var string
     */
    protected string $defaultMessage = 'Invalid data type received';
}
