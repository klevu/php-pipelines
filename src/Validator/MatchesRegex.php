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
use Klevu\Pipelines\Provider\Argument\Validator\MatchesRegexArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class MatchesRegex implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_REGULAR_EXPRESSION = MatchesRegexArgumentProvider::ARGUMENT_INDEX_REGULAR_EXPRESSION; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var MatchesRegexArgumentProvider
     */
    private readonly MatchesRegexArgumentProvider $argumentProvider;

    /**
     * @param MatchesRegexArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?MatchesRegexArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(MatchesRegexArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: MatchesRegexArgumentProvider::class,
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

        $regularExpressionArgumentValue = $this->argumentProvider->getRegularExpressionArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $result = preg_match($regularExpressionArgumentValue, $data);
        if (false === $result) {
            throw new InvalidValidationArgumentsException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Regex argument (%s) is not a valid regular expression',
                        MatchesRegexArgumentProvider::ARGUMENT_INDEX_REGULAR_EXPRESSION,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        if (0 === $result) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data does not match regular expression %s; Received %s',
                        $regularExpressionArgumentValue,
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
