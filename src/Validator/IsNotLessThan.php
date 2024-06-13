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
use Klevu\Pipelines\Validator\IsLessThan as IsLessThanValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsNotLessThan implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_VALUE = IsLessThanValidator::ARGUMENT_INDEX_VALUE;

    /**
     * @var IsLessThanValidator
     */
    private readonly IsLessThanValidator $isLessThanValidator;

    /**
     * @param IsLessThanValidator|null $isLessThanValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsLessThanValidator $isLessThanValidator = null,
    ) {
        $container = Container::getInstance();

        $isLessThanValidator ??= $container->get(IsLessThanValidator::class);
        try {
            $this->isLessThanValidator = $isLessThanValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsLessThanValidator::class,
                instance: $isLessThanValidator,
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
        if (null === $data) {
            return;
        }

        try {
            $this->isLessThanValidator->validate(
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
                    'Data %s is not less than specified value',
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
