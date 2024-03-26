<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Validator\IsEqualToArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsEqualTo implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_VALUE = IsEqualToArgumentProvider::ARGUMENT_INDEX_VALUE;
    final public const ARGUMENT_INDEX_STRICT = IsEqualToArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var IsEqualToArgumentProvider
     */
    private readonly IsEqualToArgumentProvider $argumentProvider;

    /**
     * @param IsEqualToArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsEqualToArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsEqualToArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsEqualToArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return void
     * @throws InvalidDataValidationException
     */
    public function validate(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): void {
        $valueArgumentValue = $this->argumentProvider->getValueArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $strictArgumentValue = $this->argumentProvider->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $isValid = $strictArgumentValue
            ? ($data === $valueArgumentValue)
            : ($data == $valueArgumentValue); // phpcs:ignore SlevomatCodingStandard.Operators.DisallowEqualOperators.DisallowedEqualOperator, Generic.Files.LineLength.TooLong

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data %s is not equal to %s using %s checks',
                        is_scalar($data)
                            ? json_encode($data)
                            : get_debug_type($data),
                        is_scalar($valueArgumentValue)
                            ? json_encode($valueArgumentValue)
                            : get_debug_type($valueArgumentValue),
                        $strictArgumentValue
                            ? 'strict'
                            : 'loose',
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
