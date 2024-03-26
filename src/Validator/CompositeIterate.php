<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\ObjectManager\ClassNotFoundException;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Klevu\Pipelines\Provider\Argument\Validator\CompositeIterateArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CompositeIterate implements ValidatorInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_VALIDATION = CompositeIterateArgumentProvider::ARGUMENT_INDEX_VALIDATION;

    /**
     * @var CompositeIterateArgumentProvider
     */
    private readonly CompositeIterateArgumentProvider $argumentProvider;
    /**
     * @var ValidatorManager
     */
    private readonly ValidatorManager $validatorManager;

    /**
     * @param CompositeIterateArgumentProvider|null $argumentProvider
     * @param ValidatorManager|null $validatorManager
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?CompositeIterateArgumentProvider $argumentProvider = null,
        ?ValidatorManager $validatorManager = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(CompositeIterateArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: CompositeIterateArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $validatorManager ??= $container->get(ValidatorManager::class);
        try {
            $this->validatorManager = $validatorManager; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ValidatorManager::class,
                instance: $validatorManager,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws ValidationException
     * @throws InvalidTypeValidationException
     * @throws InvalidDataValidationException
     * @throws InvalidValidationArgumentsException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        if (null === $data) {
            return;
        }

        try {
            $dataArray = $this->convertIterableToArray($data);
        } catch (\InvalidArgumentException) {
            throw new InvalidTypeValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be null|iterable; Received %s',
                        get_debug_type($data),
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $validationArgumentValue = $this->argumentProvider->getValidationArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $errors = [];
        foreach ($dataArray as $index => $dataItem) {
            /** @var Validation $validation */
            foreach ($validationArgumentValue as $validation) {
                try {
                    /** @var ValidatorInterface $validator */
                    $validator = $this->validatorManager->get($validation->validatorName);
                } catch (ClassNotFoundException $exception) {
                    throw new InvalidValidationArgumentsException(
                        validatorName: $this::class,
                        errors: [
                            sprintf(
                                'Validation %s is not a registered Validator',
                                $validation->validatorName,
                            ),
                        ],
                        arguments: $arguments,
                        data: $data,
                        previous: $exception,
                    );
                }

                try {
                    $validator->validate(
                        data: $dataItem,
                        arguments: $validation->arguments,
                        context: $context,
                    );
                } catch (ValidationException $exception) {
                    $errors[] = sprintf(
                        '%s at item %s. Errors: %s',
                        $exception->getMessage(),
                        $index,
                        implode(', ', $exception->getErrors()),
                    );
                }
            }
        }

        if ($errors) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: $errors,
                arguments: $arguments,
                data: $data,
                message: sprintf(
                    '%s items contain invalid data',
                    count($errors),
                ),
            );
        }
    }
}
