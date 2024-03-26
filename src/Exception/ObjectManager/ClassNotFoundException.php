<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\ObjectManager;

use Klevu\Pipelines\Exception\ObjectManagerException;
use Psr\Container\NotFoundExceptionInterface;

class ClassNotFoundException extends ObjectManagerException implements NotFoundExceptionInterface
{
    /**
     * @var string[]
     */
    private readonly array $namespaces;

    /**
     * @param string $identifier
     * @param string[] $namespaces
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        string $identifier,
        array $namespaces,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->namespaces = $namespaces;

        if (!$message) {
            $message = sprintf(
                'Could not locate class for identifier "%s" in namespaces "%s"',
                $identifier,
                implode(';', $namespaces),
            );
        }

        parent::__construct($identifier, $message, $code, $previous);
    }

    /**
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }
}
