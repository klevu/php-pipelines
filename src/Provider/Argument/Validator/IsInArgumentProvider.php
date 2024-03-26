<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Klevu\Pipelines\Validator\IsIn as IsInValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsInArgumentProvider
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_HAYSTACK = 0;
    final public const ARGUMENT_INDEX_STRICT = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var bool
     */
    private readonly bool $defaultStrict;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param bool $defaultStrict
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        bool $defaultStrict = false,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $this->defaultStrict = $defaultStrict;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return mixed[]
     * @throws InvalidValidationArgumentsException
     */
    public function getHaystackArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): array {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_HAYSTACK,
            defaultValue: null,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case is_iterable($argumentValue):
                $argumentValue = $this->convertIterableToArray($argumentValue);
                break;

            default:
                throw new InvalidValidationArgumentsException(
                    validatorName: IsInValidator::class,
                    errors: [
                        sprintf(
                            'Haystack argument (%s) must be iterable; Received %s',
                            self::ARGUMENT_INDEX_HAYSTACK,
                            get_debug_type($argumentValue),
                        ),
                    ],
                    arguments: $arguments,
                    data: $extractionPayload,
                );
        }

        return $argumentValue;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     * @throws InvalidValidationArgumentsException
     */
    public function getStrictArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_STRICT,
            defaultValue: $this->defaultStrict,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidValidationArgumentsException(
                validatorName: IsInValidator::class,
                errors: [
                    sprintf(
                        'Strict argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_INDEX_STRICT,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return $argumentValue;
    }
}
