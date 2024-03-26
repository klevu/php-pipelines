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
use Klevu\Pipelines\Provider\Argument\Validator\IsValidDateArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsValidDate implements ValidatorInterface
{
    public const ARGUMENT_INDEX_ALLOWED_FORMATS = IsValidDateArgumentProvider::ARGUMENT_INDEX_ALLOWED_FORMATS;

    /**
     * @var IsValidDateArgumentProvider
     */
    private IsValidDateArgumentProvider $argumentProvider;

    /**
     * @param IsValidDateArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsValidDateArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsValidDateArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsValidDateArgumentProvider::class,
                instance: $argumentProvider,
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

        $timestamp = strtotime($data);
        if (false === $timestamp) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data is not a valid date string; Received "%s"',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $allowedFormatsArgumentValue = $this->argumentProvider->getAllowedFormatsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        if (null !== $allowedFormatsArgumentValue) {
            $matchesFormat = false;
            foreach ($allowedFormatsArgumentValue as $allowedFormatValue) {
                if ($data === date($allowedFormatValue, $timestamp)) {
                    $matchesFormat = true;
                    break;
                }
            }

            if (!$matchesFormat) {
                throw new InvalidDataValidationException(
                    validatorName: $this::class,
                    errors: [
                        sprintf(
                            'Data does not match expected date format; Received "%s"',
                            $data,
                        ),
                    ],
                    arguments: $arguments,
                    data: $data,
                );
            }
        }
    }
}
