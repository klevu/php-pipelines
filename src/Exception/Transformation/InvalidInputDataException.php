<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Transformation;

use Klevu\Pipelines\Exception\TransformationException;

class InvalidInputDataException extends TransformationException
{
    /**
     * @var string
     */
    protected string $defaultMessage = 'Invalid input data for transformation';

    /**
     * @param string $transformerName
     * @param string $expectedType
     * @param string[] $errors
     * @param iterable<mixed>|null $arguments
     * @param mixed|null $data
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $transformerName,
        string $expectedType,
        array $errors = [],
        ?iterable $arguments = null,
        mixed $data = null,
        string $message = '',
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        parent::__construct(
            transformerName: $transformerName,
            errors: array_merge(
                $errors,
                [
                    sprintf(
                        'Invalid data. Expected %s, received %s',
                        $expectedType,
                        get_debug_type($data),
                    ),
                ],
            ),
            arguments: $arguments,
            data: $data,
            message: $message,
            code: $code,
            previous: $previous,
        );
    }
}
