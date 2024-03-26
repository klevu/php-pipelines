<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Stage;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\ObjectManagerException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Exception\ValidationExceptionInterface;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesNotSupportedTrait;
use Klevu\Pipelines\Validator\ValidatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Validate implements PipelineInterface
{
    use StagesNotSupportedTrait;

    public const ARGUMENT_KEY_VALIDATION = 'validation';

    /**
     * @var ValidatorManager
     */
    private readonly ValidatorManager $validatorManager;
    /**
     * @var ValidationIteratorFactory
     */
    private readonly ValidationIteratorFactory $validationIteratorFactory;
    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var ValidationIterator
     */
    private ValidationIterator $validations;

    /**
     * @param ValidatorManager|null $validatorManager
     * @param ValidationIteratorFactory|null $validationIteratorFactory
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     * @throws InvalidPipelineArgumentsException
     */
    public function __construct(
        ?ValidatorManager $validatorManager = null,
        ?ValidationIteratorFactory $validationIteratorFactory = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        if (null === $validatorManager) {
            $validatorManager = $container->get(ValidatorManager::class);
            if (!($validatorManager instanceof ValidatorManager)) {
                throw new InvalidClassException(
                    identifier: ValidatorManager::class,
                    instance: $validatorManager,
                );
            }
        }
        $this->validatorManager = $validatorManager;

        if (null === $validationIteratorFactory) {
            $validationIteratorFactory = $container->get(ValidationIteratorFactory::class);
            if (!($validationIteratorFactory instanceof ValidationIteratorFactory)) {
                throw new InvalidClassException(
                    identifier: ValidationIteratorFactory::class,
                    instance: $validationIteratorFactory,
                );
            }
        }
        $this->validationIteratorFactory = $validationIteratorFactory;

        $this->validations = new ValidationIterator([]);

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
        if (!($args[static::ARGUMENT_KEY_VALIDATION] ?? null)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $args,
                message: sprintf(
                    'Validation argument (%s) is required',
                    static::ARGUMENT_KEY_VALIDATION,
                ),
            );
        }

        $this->validations = $this->prepareValidationArgument(
            validation: $args[static::ARGUMENT_KEY_VALIDATION],
            arguments: $args,
        );
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ObjectManagerException
     * @throws ValidationException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        /** @var Validation $validation */
        foreach ($this->validations as $validation) {
            /** @var ValidatorInterface $validator */
            $validator = $this->validatorManager->get(
                $validation->validatorName,
            );

            try {
                $validator->validate(
                    data: is_object($payload) ? clone $payload : $payload,
                    arguments: $validation->arguments,
                    context: $context,
                );
            } catch (ValidationExceptionInterface $exception) {
                throw new StageException(
                    pipeline: $this,
                    previous: $exception,
                );
            }
        }

        return $payload;
    }

    /**
     * @param mixed $validation
     * @param mixed[]|null $arguments
     * @return ValidationIterator
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareValidationArgument(
        mixed $validation,
        ?array $arguments,
    ): ValidationIterator {
        if (is_array($validation)) {
            $return = new ValidationIterator();
            foreach ($validation as $validationItem) {
                $return->merge(
                    $this->prepareValidationArgument($validationItem, $arguments),
                );
            }

            return $return;
        }

        if ($validation instanceof ValidationIterator) {
            return $validation;
        }

        if ($validation instanceof Validation) {
            return new ValidationIterator([$validation]);
        }

        if (!is_string($validation)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Validation argument (%s) must be instance of %s or validation string; Received %s',
                    static::ARGUMENT_KEY_VALIDATION,
                    Validation::class,
                    get_debug_type($validation),
                ),
            );
        }

        $validations = $this->validationIteratorFactory->createFromSyntaxDeclaration(
            syntaxDeclaration: trim($validation),
        );
        if (!count($validations)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Validation argument (%s) does not contain any valid transformation strings',
                    static::ARGUMENT_KEY_VALIDATION,
                ),
            );
        }

        return $validations;
    }
}
