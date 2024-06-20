<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Pipeline;

use Klevu\Pipelines\Exception\ExtractionExceptionInterface;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\TransformationExceptionInterface;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Exception\ValidationExceptionInterface;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Parser\SyntaxParser;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesTrait;

class CreateRecord implements PipelineInterface
{
    use StagesTrait;

    public const ARGUMENT_KEY_RETURN_OBJECT = 'returnObject';

    /**
     * @var Extractor
     */
    private Extractor $extractor;
    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var bool
     */
    private bool $returnObject = false;

    /**
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @param Extractor|null $extractor
     *
     * @throws \RuntimeException
     * @throws InvalidPipelineArgumentsException
     */
    public function __construct(
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
        ?Extractor $extractor = null,
    ) {
        $container = Container::getInstance();

        array_walk($stages, [$this, 'addStage']);
        if ($args) {
            $this->setArgs($args);
        }

        $this->identifier = $identifier;

        $extractor ??= $container->get(Extractor::class);
        try {
            $this->extractor = $extractor; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: Extractor::class,
                instance: $pipelineBuilder,
            );
        }
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
     *
     * @return void
     * @throws InvalidPipelineArgumentsException
     */
    public function setArgs(array $args): void
    {
        if (array_key_exists(static::ARGUMENT_KEY_RETURN_OBJECT, $args)) {
            $this->returnObject = $this->prepareReturnObjectArgument(
                returnObject: $args[static::ARGUMENT_KEY_RETURN_OBJECT],
                arguments: $args,
            );
        }
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return object|mixed[]
     * @throws ExtractionExceptionInterface
     * @throws TransformationExceptionInterface
     * @throws ValidationExceptionInterface
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): object|array {
        $return = [];
        foreach ($this->stages as $identifier => $stage) {
            try {
                if (is_string($identifier) && str_starts_with($identifier, SyntaxParser::EXTRACTION_START_CHARACTER)) {
                    $identifier = $this->extractor->extract(
                        source: $payload,
                        accessor: substr($identifier, 1),
                        context: $context,
                    );
                }

                $return[$identifier] = $stage->execute(
                    payload: $payload,
                    context: $context,
                );
            } catch (ValidationExceptionInterface $exception) {
                throw new ValidationException(
                    validatorName: $exception->getValidatorName(),
                    errors: $exception->getErrors(),
                    data: $payload,
                    message: sprintf(
                        '%s for item %s of CreateRecord data',
                        $exception->getMessage(),
                        json_encode($identifier),
                    ),
                    code: $exception->getCode(),
                    previous: $exception,
                );
            }
        }

        if ($this->returnObject) {
            $return = (object)$return;
        }

        return $return;
    }

    /**
     * @param mixed $returnObject
     * @param mixed[]|null $arguments
     *
     * @return bool
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareReturnObjectArgument(
        mixed $returnObject,
        ?array $arguments,
    ): bool {
        if (null === $returnObject) {
            return false;
        }

        if (!is_bool($returnObject)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Return Object argument (%s) must be null or bool; Received %s',
                    static::ARGUMENT_KEY_RETURN_OBJECT,
                    get_debug_type($returnObject),
                ),
            );
        }

        return $returnObject;
    }
}
