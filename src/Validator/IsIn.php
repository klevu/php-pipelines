<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidDataValidationException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Validator\IsInArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsIn implements ValidatorInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_HAYSTACK = IsInArgumentProvider::ARGUMENT_INDEX_HAYSTACK;
    final public const ARGUMENT_INDEX_STRICT = IsInArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var IsInArgumentProvider
     */
    private readonly IsInArgumentProvider $argumentProvider;

    /**
     * @param IsInArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsInArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsInArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsInArgumentProvider::class,
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

        $haystackArgumentValue = $this->argumentProvider->getHaystackArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $strictArgumentValue = $this->argumentProvider->getStrictArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $isValid = in_array(
            needle: $data,
            haystack: $haystackArgumentValue,
            strict: $strictArgumentValue,
        );

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Value %s is not found in specified values',
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
}
