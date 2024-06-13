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
use Klevu\Pipelines\Validator\IsValidDate as IsValidDateValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsValidDateArgumentProvider
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_ALLOWED_FORMATS = 0;
    private const DEFAULT_ALLOWED_FORMATS = [
        'c', // eg 2024-12-31T15:19:21+00:00
        'Y-m-d H:i:s', // eg 2024-12-31 15:19:21
        'Y-m-d\TH:i:s.vp', // eg 2024-12-31T15:19:21.000Z
        'Y-m-d', // eg 2024-12-31
        'y-m-d', // eg 24-12-31
        'm/d/Y', // eg 12/31/2024
        'd-m-Y', // eg 31-12-2024
        'jS F Y', // eg 1st December 2024
        'jS M Y', // eg 1st Dec 2024
    ];

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string[]|null
     */
    private readonly ?array $defaultAllowedFormats;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string[]|null $defaultAllowedFormats
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?array $defaultAllowedFormats = self::DEFAULT_ALLOWED_FORMATS,
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

        $this->defaultAllowedFormats = (null === $defaultAllowedFormats)
            ? null
            : array_map('strval', $defaultAllowedFormats);
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string[]|null
     * @throws InvalidValidationArgumentsException
     */
    public function getAllowedFormatsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?array {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ALLOWED_FORMATS,
            defaultValue: $this->defaultAllowedFormats,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case null === $argumentValue:
                break;

            case is_iterable($argumentValue):
                $argumentValue = array_map(
                    static fn (mixed $allowedFormat): string => match (true) {
                        is_string($allowedFormat) => $allowedFormat,
                        default => throw new InvalidValidationArgumentsException(
                            validatorName: IsValidDateValidator::class,
                            errors: [
                                sprintf(
                                    'Allowed Formats argument (%s) value must be array of string; Received %s',
                                    static::ARGUMENT_INDEX_ALLOWED_FORMATS,
                                    get_debug_type($allowedFormat),
                                ),
                            ],
                            arguments: $arguments,
                            data: $extractionPayload,
                        ),
                    },
                    $this->convertIterableToArray($argumentValue),
                );
                break;

            default:
                throw new InvalidValidationArgumentsException(
                    validatorName: IsValidDateValidator::class,
                    errors: [
                        sprintf(
                            'Allowed Formats argument (%s) must be null|array; Received %s',
                            static::ARGUMENT_INDEX_ALLOWED_FORMATS,
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
