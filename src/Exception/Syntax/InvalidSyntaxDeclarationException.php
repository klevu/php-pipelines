<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Exception\Syntax;

use Klevu\Pipelines\Exception\SyntaxException;

class InvalidSyntaxDeclarationException extends SyntaxException
{
    /**
     * @var mixed
     */
    private readonly mixed $syntaxDeclaration;

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
        $this->syntaxDeclaration = $syntaxDeclaration;

        parent::__construct($message, $code, $previous);
    }

    /**
     * @return mixed
     */
    public function getSyntaxDeclaration(): mixed
    {
        return $this->syntaxDeclaration;
    }
}
