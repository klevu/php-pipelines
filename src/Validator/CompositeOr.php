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
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CompositeOr implements ValidatorInterface
{
    /**
     * @var ValidatorManager
     */
    private readonly ValidatorManager $validatorManager;
    /**
     * @var ValidationIteratorFactory
     */
    private readonly ValidationIteratorFactory $validationIteratorFactory;

    /**
     * @param ValidatorManager|null $validatorManager
     * @param ValidationIteratorFactory|null $validationIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ValidatorManager $validatorManager = null,
        ?ValidationIteratorFactory $validationIteratorFactory = null,
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
        $orValidations = $this->prepareArguments($arguments);

        $orValidates = false;
        $errors = [];
        /** @var Argument $orValidationsArgument */
        foreach ($orValidations as $orValidationsArgument) {
            /** @var ValidationIterator $andValidations */
            $andValidations = $orValidationsArgument->getValue();
            $andValidates = true;
            foreach ($andValidations as $andValidation) {
                /** @var Validation $andValidation */
                try {
                    /** @var ValidatorInterface $andValidator */
                    $andValidator = $this->validatorManager->get($andValidation->validatorName);
                } catch (ClassNotFoundException $exception) {
                    throw new InvalidValidationArgumentsException(
                        validatorName: $this::class,
                        errors: [
                            sprintf(
                                'Validation %s is not a registered Validator',
                                $andValidation->validatorName,
                            ),
                        ],
                        arguments: $arguments,
                        previous: $exception,
                    );
                }

                try {
                    $andValidator->validate(
                        data: $data,
                        arguments: $andValidation->arguments,
                        context: $context,
                    );
                } catch (ValidationException $exception) {
                    $errors[] = $exception->getErrors();
                    $andValidates = false;
                    continue;
                }
            }

            if ($andValidates) {
                $orValidates = true;
                break;
            }
        }

        if (!$orValidates) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: array_merge([], ...$errors),
                arguments: $arguments,
                data: $data,
                message: 'Data does not pass any registered validation conditions',
            );
        }
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @return ArgumentIterator
     * @throws InvalidValidationArgumentsException
     */
    private function prepareArguments(?ArgumentIterator $arguments): ArgumentIterator
    {
        if (null === $arguments) {
            $arguments = new ArgumentIterator();
        }
        if (!$arguments->count()) {
            throw new InvalidValidationArgumentsException(
                validatorName: $this::class,
                errors: [
                    'At least one validation argument must be provided',
                ],
                arguments: $arguments,
            );
        }

        $return = new ArgumentIterator();
        try {
            /** @var Argument $argument */
            foreach ($arguments as $index => $argument) {
                $argumentValue = $argument->getValue();

                switch (true) {
                    case $argumentValue instanceof ValidationIterator:
                        $return->addItem(
                            $this->prepareValidationArgument(
                                validationArgument: new Argument($argumentValue),
                                arguments: $arguments,
                            ),
                        );
                        break;

                    case $argumentValue instanceof Validation:
                        $return->addItem(
                            $this->prepareValidationArgument(
                                validationArgument: new Argument(new ValidationIterator([$argumentValue])),
                                arguments: $arguments,
                            ),
                        );
                        break;

                    case is_string($argumentValue):
                        $return->addItem(
                            $this->prepareValidationArgument(
                                validationArgument: new Argument(
                                    $this->validationIteratorFactory->createFromSyntaxDeclaration($argumentValue),
                                ),
                                arguments: $arguments,
                            ),
                        );
                        break;

                    case $argumentValue instanceof ArgumentIterator:
                        /** @var Argument $argumentValueItem */
                        foreach ($argumentValue as $argumentValueItem) {
                            $return->addItem(
                                $this->prepareValidationArgument(
                                    validationArgument: $argumentValueItem,
                                    arguments: $arguments,
                                ),
                            );
                        }
                        break;

                    case is_array($argumentValue):
                        foreach ($argumentValue as $argumentValueItem) {
                            $return->addItem(
                                $this->prepareValidationArgument(
                                    validationArgument: new Argument($argumentValueItem),
                                    arguments: $arguments,
                                ),
                            );
                        }
                        break;

                    default:
                        throw new InvalidValidationArgumentsException(
                            validatorName: $this::class,
                            errors: [
                                sprintf(
                                    'Invalid Validation argument at index %s. Expected %s|string|array; Received %s',
                                    $index,
                                    Validation::class,
                                    get_debug_type($argument),
                                ),
                            ],
                            arguments: $arguments,
                        );
                }
            }
        } catch (\InvalidArgumentException $exception) {
            throw new InvalidValidationArgumentsException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Invalid Validation arguments received: %s',
                        $exception->getMessage(),
                    ),
                ],
                arguments: $arguments,
                previous: $exception,
            );
        }

        return $return;
    }

    /**
     * @param Argument $validationArgument
     * @param ArgumentIterator|null $arguments
     * @return Argument
     * @throws InvalidValidationArgumentsException
     */
    private function prepareValidationArgument(
        Argument $validationArgument,
        ?ArgumentIterator $arguments,
    ): Argument {
        $validationValue = $validationArgument->getValue();

        if (null === $validationValue) {
            throw new InvalidValidationArgumentsException(
                validatorName: $this::class,
                errors: [
                    'Validation argument is required',
                ],
                arguments: $arguments,
            );
        }

        switch (true) {
            case $validationValue instanceof ValidationIterator:
                break;

            case $validationValue instanceof Validation:
                $validationArgument->setValue(
                    new ValidationIterator([$validationValue]),
                );
                break;

            case is_string($validationValue):
                $validationArgument->setValue(
                    $this->validationIteratorFactory->createFromSyntaxDeclaration($validationValue),
                );
                break;

            case is_array($validationValue):
                $newValidationArgumentValue = new ValidationIterator();
                try {
                    foreach ($validationValue as $index => $validation) {
                        $validationItemArgument = $this->prepareValidationArgument(
                            validationArgument: new Argument($validation),
                            arguments: $arguments,
                        );
                        /** @var ValidationIterator $validationItemValue */
                        $validationItemValue = $validationItemArgument->getValue();
                        $newValidationArgumentValue->merge($validationItemValue);
                    }
                } catch (InvalidValidationArgumentsException $exception) {
                    throw new InvalidValidationArgumentsException(
                        validatorName: $exception->getValidatorName(),
                        errors: [
                            sprintf(
                                '%s in item #%s',
                                $exception->getMessage(),
                                $index ?: null,
                            ),
                        ],
                        arguments: $exception->getArguments(),
                        code: $exception->getCode(),
                        previous: $exception,
                    );
                }
                $validationArgument->setValue($newValidationArgumentValue);
                break;

            default:
                throw new InvalidValidationArgumentsException(
                    validatorName: $this::class,
                    errors: [
                        sprintf(
                            'Invalid Validator argument. Expected %s|string|array; received %s',
                            ValidatorInterface::class,
                            get_debug_type($validationValue),
                        ),
                    ],
                    arguments: $arguments,
                );
        }

        /** @var ValidationIterator $validations */
        $validations = $validationArgument->getValue();
        if (!count($validations)) {
            throw new InvalidValidationArgumentsException(
                validatorName: $this::class,
                errors: [
                    'Validations cannot be empty',
                ],
                arguments: $arguments,
            );
        }

        return $validationArgument;
    }
}
