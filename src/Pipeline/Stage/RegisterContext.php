<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Stage;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Parser\ArgumentConverter;
use Klevu\Pipelines\Parser\SyntaxParser;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesNotSupportedTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class RegisterContext implements PipelineInterface
{
    use StagesNotSupportedTrait;

    public const ARGUMENT_KEY_CONTEXT_KEY = 'contextKey';

    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;
    /**
     * @var ArgumentConverter
     */
    private readonly ArgumentConverter $argumentConverter;
    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var string|Extraction
     */
    private string|Extraction $contextKeyArgument = '';

    /**
     * @param Extractor|null $extractor
     * @param ArgumentConverter|null $argumentConverter
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     * @throws InvalidPipelineArgumentsException
     */
    public function __construct(
        ?Extractor $extractor = null,
        ?ArgumentConverter $argumentConverter = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        if (null === $extractor) {
            $extractor = $container->get(Extractor::class);
            if (!($extractor instanceof Extractor)) {
                throw new InvalidClassException(
                    identifier: Extractor::class,
                    instance: $extractor,
                );
            }
        }
        $this->extractor = $extractor;

        if (null === $argumentConverter) {
            $argumentConverter = $container->get(ArgumentConverter::class);
            if (!($argumentConverter instanceof ArgumentConverter)) {
                throw new InvalidClassException(
                    identifier: ArgumentConverter::class,
                    instance: $argumentConverter,
                );
            }
        }
        $this->argumentConverter = $argumentConverter;

        array_walk($stages, [$this, 'addStage']);
        if ($args) {
            $this->setArgs($args);
        }

        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param mixed[] $args
     * @return void
     * @throws InvalidPipelineArgumentsException
     */
    public function setArgs(array $args): void
    {
        if (!($args[static::ARGUMENT_KEY_CONTEXT_KEY] ?? null)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $args,
                message: sprintf(
                    'Context Key argument (%s) is required',
                    static::ARGUMENT_KEY_CONTEXT_KEY,
                ),
            );
        }

        $this->contextKeyArgument = $this->prepareContextKeyArgument(
            contextKey: $args[static::ARGUMENT_KEY_CONTEXT_KEY],
            arguments: $args,
        );
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws InvalidPipelineArgumentsException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        $contextKey = $this->getContextKey(
            contextKeyArgument: $this->contextKeyArgument,
            payload: $payload,
            context: $context,
        );
        $context[$contextKey] = $payload;

        return $payload;
    }

    /**
     * @param mixed $contextKeyArgument
     * @param mixed $payload
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return string
     * @throws InvalidPipelineArgumentsException
     */
    private function getContextKey(
        mixed $contextKeyArgument,
        mixed $payload,
        ?\ArrayAccess $context,
    ): string {
        $contextKey = $contextKeyArgument;
        if ($contextKey instanceof Extraction) {
            $contextKey = $this->extractor->extract(
                source: $payload,
                accessor: $contextKey->accessor,
                transformations: $contextKey->transformations,
                context: $context,
            );
        }

        if (!is_string($contextKey) || !trim($contextKey)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: [
                    static::ARGUMENT_KEY_CONTEXT_KEY => $contextKeyArgument,
                ],
                message: sprintf(
                    'Context Key argument (%s) must be non-empty string (or evaluate to string); Received %s',
                    RegisterContext::ARGUMENT_KEY_CONTEXT_KEY,
                    get_debug_type($contextKey),
                ),
            );
        }

        return $contextKey;
    }

    /**
     * @param mixed $contextKey
     * @param mixed[]|null $arguments
     * @return string|Extraction
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareContextKeyArgument(
        mixed $contextKey,
        ?array $arguments,
    ): string|Extraction {
        if (
            is_string($contextKey)
            && str_starts_with($contextKey, SyntaxParser::EXTRACTION_START_CHARACTER)
        ) {
            $contextKeyArgument = $this->argumentConverter->execute($contextKey);
            $contextKey = $contextKeyArgument->getValue();
        }

        if (!is_string($contextKey) && !($contextKey instanceof Extraction)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Context Key argument (%s) must be string or extraction; Received %s',
                    static::ARGUMENT_KEY_CONTEXT_KEY,
                    get_debug_type($contextKey),
                ),
            );
        }

        return $contextKey;
    }
}
