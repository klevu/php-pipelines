<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManagerException;
use Psr\Container\ContainerExceptionInterface;

class InvalidClassException extends ObjectManagerException implements ContainerExceptionInterface
{
    /**
     * @var object|null
     */
    private readonly ?object $instance;

    /**
     * @param string $identifier
     * @param object|null $instance
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $identifier,
        ?object $instance,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->instance = $instance;

        if (!$message) {
            $message = sprintf(
                'Class of type "%s" identified by "%s" is not valid',
                $instance ? $instance::class : 'null',
                $identifier,
            );
        }

        parent::__construct($identifier, $message, $code, $previous);
    }

    /**
     * @return object|null
     */
    public function getInstance(): ?object
    {
        return $this->instance;
    }
}
