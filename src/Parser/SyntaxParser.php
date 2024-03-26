<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Parser;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\SyntaxItem;
use Klevu\Pipelines\Model\SyntaxItemIterator;
use Klevu\Pipelines\Model\TransformationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SyntaxParser
{
    public const ESCAPE_CHARACTER = '\\';
    public const STRING_QUOTE_CHARACTER = '"';
    public const EXTRACTION_START_CHARACTER = '$';
    public const EXTRACTION_TRANSFORM_CHARACTER = '|';
    public const ARRAY_OPEN_CHARACTER = '[';
    public const ARRAY_CLOSE_CHARACTER = ']';
    public const OBJECT_OPEN_CHARACTER = '{';
    public const OBJECT_CLOSE_CHARACTER = '}';

    /**
     * @var CommandsSplitter
     */
    private readonly CommandsSplitter $commandsSplitter;
    /**
     * @var ArgumentsSplitter
     */
    private readonly ArgumentsSplitter $argumentsSplitter;
    /**
     * @var ArgumentConverter
     */
    private readonly ArgumentConverter $argumentConverter;
    /**
     * @var TransformationIteratorFactory
     */
    private readonly TransformationIteratorFactory $transformationIteratorFactory;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;

    /**
     * @param CommandsSplitter|null $commandsSplitter
     * @param ArgumentsSplitter|null $argumentsSplitter
     * @param ArgumentConverter|null $argumentConverter
     * @param TransformationIteratorFactory|null $transformationIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?CommandsSplitter $commandsSplitter = null,
        ?ArgumentsSplitter $argumentsSplitter = null,
        ?ArgumentConverter $argumentConverter = null,
        ?TransformationIteratorFactory $transformationIteratorFactory = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
    ) {
        $container = Container::getInstance();

        if (null === $commandsSplitter) {
            $commandsSplitter = $container->get(CommandsSplitter::class);
            if (!($commandsSplitter instanceof CommandsSplitter)) {
                throw new InvalidClassException(
                    identifier: CommandsSplitter::class,
                    instance: $commandsSplitter,
                );
            }
        }
        $this->commandsSplitter = $commandsSplitter;

        if (null === $argumentsSplitter) {
            $argumentsSplitter = $container->get(ArgumentsSplitter::class);
            if (!($argumentsSplitter instanceof ArgumentsSplitter)) {
                throw new InvalidClassException(
                    identifier: ArgumentsSplitter::class,
                    instance: $argumentsSplitter,
                );
            }
        }
        $this->argumentsSplitter = $argumentsSplitter;

        $this->transformationIteratorFactory = $transformationIteratorFactory
            ?: new TransformationIteratorFactory(
                syntaxParser: $this,
            );
        $this->argumentConverter = $argumentConverter
            ?: new ArgumentConverter(
                transformationIteratorFactory: $this->transformationIteratorFactory,
            );

        if (null === $argumentIteratorFactory) {
            $argumentIteratorFactory = $container->get(ArgumentIteratorFactory::class);
            if (!($argumentIteratorFactory instanceof ArgumentIteratorFactory)) {
                throw new InvalidClassException(
                    identifier: ArgumentIteratorFactory::class,
                    instance: $argumentIteratorFactory,
                );
            }
        }
        $this->argumentIteratorFactory = $argumentIteratorFactory;
    }

    /**
     * @param string|null $syntax
     * @return SyntaxItemIterator
     */
    public function parse(?string $syntax): SyntaxItemIterator
    {
        return new SyntaxItemIterator(array_map(
            fn (array $syntaxItemData): SyntaxItem => new SyntaxItem(
                command: $syntaxItemData['command'] ?? '',
                arguments: $this->parseArguments(
                    argumentsString: $syntaxItemData['arguments'] ?? null,
                ),
            ),
            $this->commandsSplitter->execute($syntax),
        ));
    }

    /**
     * @param string|null $argumentsString
     * @return ArgumentIterator|null
     */
    private function parseArguments(
        ?string $argumentsString,
    ): ?ArgumentIterator {
        $argumentsString = trim((string)$argumentsString);
        if ('' === $argumentsString) {
            return null;
        }

        return $this->argumentIteratorFactory->create(
            array_map(
                fn (string $argument): Argument => $this->argumentConverter->execute($argument),
                $this->argumentsSplitter->execute($argumentsString),
            ),
        );
    }
}
