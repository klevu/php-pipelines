<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception;

use Psr\Container\ContainerExceptionInterface;

class ObjectManagerException extends \LogicException implements ContainerExceptionInterface
{
    /**
     * @var string
     */
    private readonly string $identifier;

    /**
     * @param string $identifier
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $identifier,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->identifier = $identifier;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }
}
