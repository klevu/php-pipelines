<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Model;

use Klevu\Pipelines\Exception\SyntaxException;
use Klevu\Pipelines\Parser\SyntaxParser;

class TransformationIteratorFactory
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
        $this->syntaxParser = $syntaxParser ?: new SyntaxParser(
            transformationIteratorFactory: $this,
        );
    }

    /**
     * @param string $syntaxDeclaration
     * @return TransformationIterator
     * @throws SyntaxException
     */
    public function createFromSyntaxDeclaration(
        string $syntaxDeclaration,
    ): TransformationIterator {
        $syntaxItems = $this->syntaxParser->parse($syntaxDeclaration);

        return new TransformationIterator(
            array_map(
                static fn (SyntaxItem $syntaxItem): Transformation => new Transformation(
                    transformerName: $syntaxItem->command,
                    arguments: $syntaxItem->arguments,
                ),
                $syntaxItems->toArray(),
            ),
        );
    }
}
