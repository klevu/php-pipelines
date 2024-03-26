<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\ObjectManager;

class DependencyResolutionException extends ObjectInstantiationException
{
    /**
     * @var string
     */
    private readonly string $dependency;

    /**
     * @param string $dependency
     * @param string $identifier
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $dependency,
        string $identifier,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->dependency = $dependency;

        parent::__construct($identifier, $message, $code, $previous);
    }

    /**
     * @return string
     */
    public function getDependency(): string
    {
        return $this->dependency;
    }
}
