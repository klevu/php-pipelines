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

class StaticValue implements PipelineInterface
{
    use StagesNotSupportedTrait;

    final public const ARGUMENT_KEY_VALUE = 'value';

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
     * @var mixed
     */
    private mixed $valueArgument = null;

    /**
     * @param Extractor|null $extractor
     * @param ArgumentConverter|null $argumentConverter
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?Extractor $extractor = null,
        ?ArgumentConverter $argumentConverter = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        $extractor ??= $container->get(Extractor::class);
        try {
            $this->extractor = $extractor; // @phpstan-ignore-line Invalid type handled by TypeError catch
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: Extractor::class,
                instance: $extractor,
            );
        }

        $argumentConverter ??= $container->get(ArgumentConverter::class);
        try {
            $this->argumentConverter = $argumentConverter; // @phpstan-ignore-line Invalid type handled by TypeError
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentConverter::class,
                instance: $argumentConverter,
            );
        }

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
        $this->valueArgument = $this->prepareValueArgument(
            value: $args[self::ARGUMENT_KEY_VALUE],
            arguments: $args,
        );
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return mixed
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        return $this->getValue(
            valueArgument: $this->valueArgument,
            payload: $payload,
            context: $context,
        );
    }

    /**
     * @param mixed $valueArgument
     * @param mixed $payload
     * @param \ArrayAccess<int|string, mixed>|null $context
     * @return mixed
     */
    private function getValue(
        mixed $valueArgument,
        mixed $payload,
        ?\ArrayAccess $context,
    ): mixed {
        $value = $valueArgument;
        if ($value instanceof Extraction) {
            $value = $this->extractor->extract(
                source: $payload,
                accessor: $value->accessor,
                transformations: $value->transformations,
                context: $context,
            );
        }

        return $value;
    }

    /**
     * @param mixed $value
     * @param mixed[]|null $arguments
     * @return mixed
     */
    private function prepareValueArgument(
        mixed $value,
        ?array $arguments, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): mixed {
        if (
            is_string($value)
            && str_starts_with($value, SyntaxParser::EXTRACTION_START_CHARACTER)
        ) {
            $valueArgument = $this->argumentConverter->execute($value);
            $value = $valueArgument->getValue();
        }

        return $value;
    }
}
