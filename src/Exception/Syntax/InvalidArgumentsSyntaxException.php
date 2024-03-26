<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Syntax;

use Klevu\Pipelines\Exception\SyntaxException;

class InvalidArgumentsSyntaxException extends SyntaxException
{
    /**
     * @var mixed
     */
    private readonly mixed $argumentsSyntax;

    /**
     * @param mixed $syntaxDeclaration
     * @param string $message
     * @param int $code
     * @param \Throwable|null $previous
     */
    public function __construct(
        mixed $syntaxDeclaration,
        string $message = "",
        int $code = 0,
        ?\Throwable $previous = null,
    ) {
        $this->argumentsSyntax = $syntaxDeclaration;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getArgumentsSyntax(): mixed
    {
        return $this->argumentsSyntax;
    }
}
