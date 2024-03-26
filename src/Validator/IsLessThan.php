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
use Klevu\Pipelines\Provider\Argument\Validator\IsLessThanArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsLessThan implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_VALUE = IsLessThanArgumentProvider::ARGUMENT_INDEX_VALUE;

    /**
     * @var IsLessThanArgumentProvider
     */
    private readonly IsLessThanArgumentProvider $argumentProvider;

    /**
     * @param IsLessThanArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsLessThanArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsLessThanArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsLessThanArgumentProvider::class,
                instance: $argumentProvider,
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

        if (!is_numeric($data)) {
            throw new InvalidTypeValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be numeric; Received %s',
                        is_scalar($data)
                            ? json_encode($data)
                            : get_debug_type($data),
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $valueArgumentValue = $this->argumentProvider->getValueArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $isValid = ($data < $valueArgumentValue);

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data %s is not less than %s',
                        $data,
                        $valueArgumentValue,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
