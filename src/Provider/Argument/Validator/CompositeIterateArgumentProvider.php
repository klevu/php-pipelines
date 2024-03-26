<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Klevu\Pipelines\Validator\CompositeIterate as CompositeIterateValidator;
use Klevu\Pipelines\Validator\ValidatorInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class CompositeIterateArgumentProvider
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_VALIDATION = 0;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var ValidationIteratorFactory
     */
    private readonly ValidationIteratorFactory $validationIteratorFactory;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param ValidationIteratorFactory|null $validationIteratorFactory
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?ValidationIteratorFactory $validationIteratorFactory = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $validationIteratorFactory ??= $container->get(ValidationIteratorFactory::class);
        try {
            $this->validationIteratorFactory = $validationIteratorFactory; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ValidationIteratorFactory::class,
                instance: $validationIteratorFactory,
            );
        }
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return ValidationIterator
     */
    public function getValidationArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ValidationIterator {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_VALIDATION,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        return $this->convertArgumentValueToValidationIterator(
            argumentValue: $argumentValue,
            arguments: $arguments,
        );
    }

    /**
     * Separate function to allow recursive calls
     *
     * @param mixed $argumentValue
     * @param ArgumentIterator|null $arguments
     * @return ValidationIterator
     */
    private function convertArgumentValueToValidationIterator(
        mixed $argumentValue,
        ?ArgumentIterator $arguments,
    ): ValidationIterator {
        switch (true) {
            case !$argumentValue:
                throw new InvalidValidationArgumentsException(
                    validatorName: CompositeIterateValidator::class,
                    errors: [
                        sprintf(
                            'Validation argument (%s) is required',
                            self::ARGUMENT_INDEX_VALIDATION,
                        ),
                    ],
                    arguments: $arguments,
                );

            case $argumentValue instanceof ValidationIterator:
                break;

            case $argumentValue instanceof Validation:
                $argumentValue = new ValidationIterator([$argumentValue]);
                break;

            case is_string($argumentValue):
                $argumentValue = $this->validationIteratorFactory->createFromSyntaxDeclaration(
                    syntaxDeclaration: $argumentValue,
                );
                break;

            case is_iterable($argumentValue):
                $argumentValue = $this->convertIterableToArray($argumentValue);
                $validationIterator = new ValidationIterator();

                try {
                    foreach ($argumentValue as $index => $validation) {
                        $convertedValidation = $this->convertArgumentValueToValidationIterator(
                            argumentValue: $validation,
                            arguments: $arguments,
                        );
                        $validationIterator->merge($convertedValidation);
                    }
                } catch (InvalidValidationArgumentsException $exception) {
                    throw new InvalidValidationArgumentsException(
                        validatorName: $exception->getValidatorName(),
                        errors: [
                            sprintf(
                                '%s in item #%s',
                                $exception->getMessage(),
                                $index ?: '',
                            ),
                        ],
                        arguments: $exception->getArguments(),
                        code: $exception->getCode(),
                        previous: $exception,
                    );
                }
                $argumentValue = $validationIterator;
                break;

            default:
                throw new InvalidValidationArgumentsException(
                    validatorName: CompositeIterateValidator::class,
                    errors: [
                        sprintf(
                            'Invalid Validator argument (%s). Expected %s|string|array; received %s',
                            self::ARGUMENT_INDEX_VALIDATION,
                            ValidatorInterface::class,
                            get_debug_type($argumentValue),
                        ),
                    ],
                    arguments: $arguments,
                );
        }

        return $argumentValue;
    }
}
