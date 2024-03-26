<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Validation;

use Klevu\Pipelines\Exception\ValidationException;

/**
 * Specific validation exception thrown when the data being checked does not match what is expected
 * Example failure conditions include missing keys, empty values, incorrect data types within a larger structure
 */
class InvalidDataValidationException extends ValidationException
{
    /**
     * @var string
     */
    protected string $defaultMessage = 'Data is not valid';
}
