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
use Klevu\Pipelines\Provider\Argument\Validator\ContainsArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Contains implements ValidatorInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_NEEDLE = ContainsArgumentProvider::ARGUMENT_INDEX_NEEDLE;
    final public const ARGUMENT_INDEX_STRICT = ContainsArgumentProvider::ARGUMENT_INDEX_STRICT;

    /**
     * @var ContainsArgumentProvider
     */
    private readonly ContainsArgumentProvider $argumentProvider;

    /**
     * @param ContainsArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ContainsArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ContainsArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ContainsArgumentProvider::class,
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
        if (!is_iterable($data)) {
            throw new InvalidTypeValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be iterable; Received %s',
                        get_debug_type($data),
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $needleArgumentValue = $this->argumentProvider->getNeedleArgumentValue(
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
            needle: $needleArgumentValue,
            haystack: $this->convertIterableToArray($data),
            strict: $strictArgumentValue,
        );

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data does not contain value %s',
                        is_scalar($needleArgumentValue)
                            ? json_encode($needleArgumentValue)
                            : get_debug_type($needleArgumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
