<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

use Klevu\Pipelines\Exception\SyntaxException;
use Klevu\Pipelines\Parser\SyntaxParser;

class ValidationIteratorFactory
{
    /**
     * @var SyntaxParser
     */
    private readonly SyntaxParser $syntaxParser;

    /**
     * @param SyntaxParser|null $syntaxParser
     */
    public function __construct(
        ?SyntaxParser $syntaxParser = null,
    ) {
        $this->syntaxParser = $syntaxParser ?: new SyntaxParser();
    }

    /**
     * @param string $syntaxDeclaration
     * @return ValidationIterator
     * @throws SyntaxException
     */
    public function createFromSyntaxDeclaration(
        string $syntaxDeclaration,
    ): ValidationIterator {
        $syntaxItems = $this->syntaxParser->parse($syntaxDeclaration);

        return new ValidationIterator(
            array_map(
                static fn (SyntaxItem $syntaxItem): Validation => new Validation(
                    validatorName: $syntaxItem->command,
                    arguments: $syntaxItem->arguments,
                ),
                $syntaxItems->toArray(),
            ),
        );
    }
}
