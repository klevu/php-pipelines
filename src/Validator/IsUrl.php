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
use Klevu\Pipelines\Provider\Argument\Validator\IsUrlArgumentProvider;

class IsUrl implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_REQUIRE_PROTOCOL = IsUrlArgumentProvider::ARGUMENT_INDEX_REQUIRE_PROTOCOL;

    private readonly IsUrlArgumentProvider $argumentProvider;

    public function __construct(
        ?IsUrlArgumentProvider $argumentProvider,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsUrlArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsUrlArgumentProvider::class,
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

        if (filter_var($data, FILTER_VALIDATE_URL)) {
            return;
        }

        $requireProtocolArgumentValue = $this->argumentProvider->getRequireProtocolArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $isValid = !$requireProtocolArgumentValue
            && filter_var('https://' . $data, FILTER_VALIDATE_URL);

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be valid URL; Received %s',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
