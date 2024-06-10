<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;

/**
 * Validates whether passed data is numeric
 * Arguments
 *  (none)
 */
class IsNumeric implements ValidatorInterface
{
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
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        switch (true) {
            case null === $data:
            case is_numeric($data):
                return;

            case is_string($data):
                throw new InvalidDataValidationException(
                    validatorName: $this::class,
                    errors: [
                        sprintf(
                            'Data must be numeric; Received %s',
                            var_export($data, true),
                        ),
                    ],
                    arguments: $arguments,
                    data: $data,
                );

            default:
                throw new InvalidTypeValidationException(
                    validatorName: $this::class,
                    errors: [
                        sprintf(
                            'Data must be null|int|float|string; Received %s',
                            get_debug_type($data),
                        ),
                    ],
                    arguments: $arguments,
                    data: $data,
                );
        }
    }
}
