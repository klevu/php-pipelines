<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Provider\Argument\Validator\IsInArgumentProvider;
use Klevu\Pipelines\Validator\IsIn as IsInValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsNotIn implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_HAYSTACK = IsInArgumentProvider::ARGUMENT_INDEX_HAYSTACK;
    final public const ARGUMENT_INDEX_STRICT = IsInArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var IsIn
     */
    private readonly IsInValidator $isInValidator;

    /**
     * @param IsInArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsInArgumentProvider $argumentProvider = null,
    ) {
        $this->isInValidator = new IsInValidator(
            argumentProvider: $argumentProvider,
        );
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws ValidationException
     * @throws InvalidDataValidationException
     * @throws InvalidValidationArgumentsException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        try {
            $this->isInValidator->validate(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidDataValidationException) {
            // We're checking the inverse, so a validation exception here
            //  is a success condition for us
            // We don't catch the parent exception class, though, as we need
            //  to ensure invalid arguments still fail

            return;
        }

        throw new InvalidDataValidationException(
            validatorName: $this::class,
            errors: [
                sprintf(
                    'Value %s is found in specified values',
                    is_scalar($data)
                        ? '"' . $data . '"'
                        : get_debug_type($data),
                ),
            ],
            arguments: $arguments,
            data: $data,
        );
    }
}
