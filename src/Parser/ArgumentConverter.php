<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Parser;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Syntax\InvalidSyntaxDeclarationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\TransformationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ArgumentConverter
{
    /**
     * @var ArgumentsSplitter
     */
    private readonly ArgumentsSplitter $argumentsSplitter;
    /**
     * @var TransformationIteratorFactory
     */
    private readonly TransformationIteratorFactory $transformationIteratorFactory;

    /**
     * @param ArgumentsSplitter|null $argumentsSplitter
     * @param TransformationIteratorFactory|null $transformationIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentsSplitter $argumentsSplitter = null,
        ?TransformationIteratorFactory $transformationIteratorFactory = null,
    ) {
        $container = Container::getInstance();

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

        if (null === $transformationIteratorFactory) {
            $transformationIteratorFactory = $container->get(TransformationIteratorFactory::class);
            if (!($transformationIteratorFactory instanceof TransformationIteratorFactory)) {
                throw new InvalidClassException(
                    identifier: TransformationIteratorFactory::class,
                    instance: $transformationIteratorFactory,
                );
            }
        }
        $this->transformationIteratorFactory = $transformationIteratorFactory;
    }

    /**
     * @param string $argument
     * @return Argument
     * @throws InvalidSyntaxDeclarationException
     */
    public function execute(string $argument): Argument
    {
        $argument = trim($argument);

        return match (true) {
            $this->isString($argument) => $this->processString($argument),
            $this->isExtraction($argument) => $this->processExtraction($argument),
            $this->isArray($argument) => $this->processArray($argument),
            $this->isObject($argument) => $this->processObject($argument),
            default => $this->processScalar($argument),
        };
    }

    /**
     * @param string $argument
     * @return bool
     * @throws InvalidSyntaxDeclarationException
     */
    private function isString(string $argument): bool
    {
        if (!str_starts_with($argument, SyntaxParser::STRING_QUOTE_CHARACTER)) {
            return false;
        }

        if (
            !str_ends_with($argument, SyntaxParser::STRING_QUOTE_CHARACTER)
            || str_ends_with($argument, SyntaxParser::ESCAPE_CHARACTER . SyntaxParser::STRING_QUOTE_CHARACTER)
        ) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argument,
                message: sprintf(
                    'Unclosed string quote in argument: %s',
                    $argument,
                ),
            );
        }

        return true;
    }

    /**
     * @param string $argument
     * @return Argument
     */
    private function processString(string $argument): Argument
    {
        $keyValue = $this->argumentsSplitter->splitKeyValueString($argument);

        return new Argument(
            value: str_replace(
                ['\\"', '\\\\'],
                ['"', '\\'],
                substr(
                    string: (string)$keyValue['value'],
                    offset: 1,
                    length: -1,
                ),
            ),
            key: $keyValue['key'],
        );
    }

    /**
     * @param string $argument
     * @return bool
     */
    private function isExtraction(string $argument): bool
    {
        return str_starts_with($argument, SyntaxParser::EXTRACTION_START_CHARACTER);
    }

    /**
     * @param string $argument
     * @return Argument
     */
    private function processExtraction(string $argument): Argument
    {
        $keyValue = $this->argumentsSplitter->splitKeyValueString($argument);

        $argumentParts = explode(
            separator: SyntaxParser::EXTRACTION_TRANSFORM_CHARACTER,
            string: substr((string)$keyValue['value'], 1),
            limit: 2,
        );

        $accessor = $argumentParts[0] ?? null;
        $transformations = $argumentParts[1] ?? null;

        if ($transformations) {
            $transformations = $this->transformationIteratorFactory
                ->createFromSyntaxDeclaration($transformations);
        }

        return new Argument(
            value: new Extraction(
                accessor: $accessor,
                transformations: $transformations ?: null,
            ),
            key: $keyValue['key'],
        );
    }

    /**
     * @param string $argument
     * @return bool
     */
    private function isArray(string $argument): bool
    {
        if (!str_starts_with($argument, SyntaxParser::ARRAY_OPEN_CHARACTER)) {
            return false;
        }

        if (!str_ends_with($argument, SyntaxParser::ARRAY_CLOSE_CHARACTER)) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argument,
                message: sprintf(
                    'Unclosed array in argument: %s',
                    $argument,
                ),
            );
        }

        return true;
    }

    /**
     * @param string $argument
     * @return Argument
     */
    private function processArray(string $argument): Argument
    {
        $keyValue = $this->argumentsSplitter->splitKeyValueString($argument);

        $arguments = substr(
            string: (string)$keyValue['value'],
            offset: 1,
            length: -1,
        );

        return new Argument(
            value: new ArgumentIterator(
                array_map(
                    [$this, 'execute'],
                    $this->argumentsSplitter->execute($arguments),
                ),
            ),
            key: $keyValue['key'],
        );
    }

    /**
     * @param string $argument
     * @return bool
     */
    private function isObject(string $argument): bool
    {
        if (!str_starts_with($argument, SyntaxParser::OBJECT_OPEN_CHARACTER)) {
            return false;
        }

        if (!str_ends_with($argument, SyntaxParser::OBJECT_CLOSE_CHARACTER)) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argument,
                message: sprintf(
                    'Unclosed object in argument: %s',
                    $argument,
                ),
            );
        }

        return true;
    }

    /**
     * @param string $argument
     * @return Argument
     */
    private function processObject(string $argument): Argument
    {
        $argumentsArray = $this->argumentsSplitter->execute(
            substr($argument, 1, -1),
        );

        $argumentIterator = new ArgumentIterator();
        foreach ($argumentsArray as $argumentString) {
            $keyValue = $this->argumentsSplitter->splitKeyValueString(
                $argumentString,
            );

            $argumentIterator->addItem(
                new Argument(
                    value: $this->execute((string)$keyValue['value'])->getValue(),
                    key: $keyValue['key'] ? $this->execute($keyValue['key'])->getValue() : null,
                ),
            );
        }

        return new Argument(
            value: $argumentIterator,
            key: null,
        );
    }

    /**
     * @param string $argument
     * @return Argument
     * @throws InvalidSyntaxDeclarationException
     */
    private function processScalar(string $argument): Argument
    {
        $keyValue = $this->argumentsSplitter->splitKeyValueString($argument);
        $keyValue['value'] = strtolower((string)$keyValue['value']);

        $value = match (true) {
            ('false' === $keyValue['value']) => false,
            ('true' === $keyValue['value']) => true,
            ('' === $keyValue['value']) => null,
            ('null' === $keyValue['value']) => null,
            ctype_digit($keyValue['value']) => (int)$keyValue['value'],
            is_numeric($keyValue['value']) => (float)$keyValue['value'],
            default => throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argument,
                message: sprintf(
                    'Could not parse argument: %s',
                    $argument,
                ),
            ),
        };

        return new Argument(
            value: $value,
            key: $keyValue['key'],
        );
    }
}
