<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidTypeValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Validator\IsGreaterThan as IsGreaterThanValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsNotGreaterThan implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_VALUE = IsGreaterThanValidator::ARGUMENT_INDEX_VALUE;

    /**
     * @var IsGreaterThanValidator
     */
    private readonly IsGreaterThanValidator $isGreaterThanValidator;

    /**
     * @param IsGreaterThan|null $isGreaterThanValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsGreaterThanValidator $isGreaterThanValidator = null,
    ) {
        $container = Container::getInstance();

        $isGreaterThanValidator ??= $container->get(IsGreaterThanValidator::class);
        try {
            $this->isGreaterThanValidator = $isGreaterThanValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsGreaterThanValidator::class,
                instance: $isGreaterThanValidator,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws InvalidTypeValidationException
     * @throws InvalidDataValidationException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        try {
            $this->isGreaterThanValidator->validate(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidDataValidationException) {
            return;
        }

        throw new InvalidDataValidationException(
            validatorName: $this::class,
            errors: [
                sprintf(
                    'Data %s is not greater than specified value',
                    is_scalar($data)
                        ? json_encode($data)
                        : get_debug_type($data),
                ),
            ],
            arguments: $arguments,
            data: $data,
        );
    }
}
