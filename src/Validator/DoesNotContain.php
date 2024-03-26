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
use Klevu\Pipelines\Validator\Contains as ContainsValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DoesNotContain implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_NEEDLE = ContainsValidator::ARGUMENT_INDEX_NEEDLE;
    final public const ARGUMENT_INDEX_STRICT = ContainsValidator::ARGUMENT_INDEX_STRICT;

    /**
     * @var ContainsValidator
     */
    private readonly ContainsValidator $containsValidator;

    /**
     * @param Contains|null $containsValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ContainsValidator $containsValidator = null,
    ) {
        $container = Container::getInstance();

        $containsValidator ??= $container->get(ContainsValidator::class);
        try {
            $this->containsValidator = $containsValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ContainsValidator::class,
                instance: $containsValidator,
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
            $this->containsValidator->validate(
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
                'Value found in data',
            ],
            arguments: $arguments,
            data: $data,
        );
    }
}
