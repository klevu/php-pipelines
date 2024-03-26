<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Transformation;

use Klevu\Pipelines\Exception\TransformationException;

class InvalidTransformationArgumentsException extends TransformationException
{
    /**
     * @var string
     */
    protected string $defaultMessage = 'Invalid argument for transformation';
}
