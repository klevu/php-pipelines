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
use Klevu\Pipelines\Provider\Argument\Validator\IsNumericArgumentProvider;
use Psr\Container\ContainerExceptionInterface;

/**
 * Validates whether passed data is numeric
 * Arguments
 *  - <bool> Decimal Only
 */
class IsNumeric implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_DECIMAL_ONLY = IsNumericArgumentProvider::ARGUMENT_INDEX_DECIMAL_ONLY;

    /**
     * @var IsNumericArgumentProvider
     */
    private readonly IsNumericArgumentProvider $argumentProvider;

    /**
     * @param IsNumericArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws \Psr\Container\NotFoundExceptionInterface
     */
    public function __construct(
        ?IsNumericArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsNumericArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsNumericArgumentProvider::class,
                instance: $argumentProvider,
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
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        if (null === $data || is_int($data) || is_float($data)) {
            return;
        }

        if (!is_string($data)) {
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

        $decimalOnlyArgumentValue = $this->argumentProvider->getDecimalOnlyArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        if (
            (!$decimalOnlyArgumentValue && !is_numeric($data))
            || !preg_match('/^[0-9\.\-]+$/', $data)
        ) {
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
        }
    }
}
