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
use Klevu\Pipelines\Validator\MatchesRegex as MatchesRegexValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class DoesNotMatchRegex implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_REGULAR_EXPRESSION = MatchesRegexValidator::ARGUMENT_INDEX_REGULAR_EXPRESSION; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var MatchesRegexValidator
     */
    private readonly MatchesRegexValidator $matchesRegexValidator;

    /**
     * @param MatchesRegexValidator|null $matchesRegexValidator
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?MatchesRegexValidator $matchesRegexValidator = null,
    ) {
        $container = Container::getInstance();

        $matchesRegexValidator ??= $container->get(MatchesRegexValidator::class);
        try {
            $this->matchesRegexValidator = $matchesRegexValidator; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: MatchesRegexValidator::class,
                instance: $matchesRegexValidator,
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
            $this->matchesRegexValidator->validate(
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
                    'Data %s does not match regular expression',
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
