<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Pipeline;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Exception\ValidationExceptionInterface;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesTrait;

class CreateRecord implements PipelineInterface
{
    use StagesTrait;

    public const ARGUMENT_KEY_RETURN_OBJECT = 'returnObject';

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
     * @throws \RuntimeException
     * @throws InvalidPipelineArgumentsException
     */
    public function __construct(
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
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
     * @return object|mixed[]
     * @throws ExtractionException
     * @throws TransformationException
     * @throws ValidationException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): object|array {
        $return = [];
        foreach ($this->stages as $identifier => $stage) {
            try {
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
