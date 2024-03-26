<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;

class IsEmail implements ValidatorInterface
{
    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws ValidationException
     * @throws InvalidTypeValidationException
     * @throws InvalidDataValidationException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter, Generic.Files.LineLength.TooLong
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        if (null === $data) {
            return;
        }

        if (!is_string($data)) {
            throw new InvalidTypeValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be null|string; Received %s',
                        get_debug_type($data),
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        if (!filter_var($data, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be valid email; Received %s',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
