<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Validator\IsPositiveNumberArgumentProvider;
use Klevu\Pipelines\Validator\IsNumeric as IsNumericValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Validates whether passed data is positive number
 * Arguments:
 *  - <bool> Allow Zero
 */
class IsPositiveNumber implements ValidatorInterface
{
    public const ARGUMENT_INDEX_ALLOW_ZERO = IsPositiveNumberArgumentProvider::ARGUMENT_INDEX_ALLOW_ZERO;

    /**
     * @var IsPositiveNumberArgumentProvider
     */
    private readonly IsPositiveNumberArgumentProvider $argumentProvider;
    /**
     * @var ValidatorInterface
     */
    private readonly ValidatorInterface $isNumericValidator;

    /**
     * @param IsPositiveNumberArgumentProvider|null $argumentProvider
     * @param ValidatorInterface|null $isNumericValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsPositiveNumberArgumentProvider $argumentProvider = null,
        ?ValidatorInterface $isNumericValidator = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsPositiveNumberArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsPositiveNumberArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $isNumericValidator ??= $container->get(IsNumericValidator::class);
        try {
            $this->isNumericValidator = $isNumericValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsNumericValidator::class,
                instance: $isNumericValidator,
            );
        }
    }

    /**
     * {@inheritdoc}
     *
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws ValidationException
     * @throws InvalidValidationArgumentsException
     * @throws InvalidTypeValidationException
     * @throws InvalidDataValidationException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        if (null === $data) {
            return;
        }

        $this->isNumericValidator->validate(
            data: $data,
            context: $context,
        );

        $allowZeroArgumentValue = $this->argumentProvider->getAllowZeroArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        /** @var float|int|string|null $data Type hinting for PHPStan following validation */
        if ($allowZeroArgumentValue && $data < 0) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be greater than or equal to zero; received %s',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        } elseif (!$allowZeroArgumentValue && $data <= 0) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be greater than zero; received %s',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
