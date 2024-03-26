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
use Klevu\Pipelines\Validator\IsEqualTo as IsEqualToValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsNotEqualTo implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_VALUE = IsEqualToValidator::ARGUMENT_INDEX_VALUE;
    final public const ARGUMENT_INDEX_STRICT = IsEqualToValidator::ARGUMENT_INDEX_STRICT;

    /**
     * @var IsEqualToValidator
     */
    private readonly IsEqualToValidator $isEqualToValidator;

    /**
     * @param IsEqualTo|null $isEqualToValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsEqualToValidator $isEqualToValidator = null,
    ) {
        $container = Container::getInstance();

        $isEqualToValidator ??= $container->get(IsEqualToValidator::class);
        try {
            $this->isEqualToValidator = $isEqualToValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsEqualToValidator::class,
                instance: $isEqualToValidator,
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
            $this->isEqualToValidator->validate(
                data: $data,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidDataValidationException) {
            return;
        }

        $valueArgument = $arguments?->getByKey(self::ARGUMENT_INDEX_VALUE);
        $value = $valueArgument?->getValue();

        throw new InvalidDataValidationException(
            validatorName: $this::class,
            errors: [
                sprintf(
                    'Data is equal to value [%s]',
                    json_encode($value),
                ),
            ],
            arguments: $arguments,
            data: $data,
        );
    }
}
