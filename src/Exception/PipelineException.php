<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

class PipelineException extends \LogicException
{
    /**
     * @var string|null
     */
    private readonly ?string $pipelineName;

    /**
     * @param string|null $pipelineName
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        ?string $pipelineName,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->pipelineName = $pipelineName;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string|null
     */
    public function getPipelineName(): ?string
    {
        return $this->pipelineName;
    }
}
