<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Pipeline;

use Klevu\Pipelines\Exception\PipelineException;

class InvalidPipelineArgumentsException extends PipelineException
{
    /**
     * @var mixed
     */
    private readonly mixed $arguments;

    /**
     * @param string $pipelineName
     * @param mixed $arguments
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $pipelineName,
        mixed $arguments,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->arguments = $arguments;

        $message = sprintf(
            'Invalid arguments for pipeline "%s". %s',
            $pipelineName,
            $message,
        );

        parent::__construct(
            pipelineName: $pipelineName,
            message: $message,
            code: $code,
            previous: $previous,
        );
    }

    /**
     * @return mixed
     */
    public function getArguments(): mixed
    {
        return $this->arguments;
    }
}
