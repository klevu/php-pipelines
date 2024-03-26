<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Validator;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Validation\InvalidValidationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Validation\IsIpAddress\Versions;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Klevu\Pipelines\Validator\IsIpAddress as IsIpAddressValidator;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsIpAddressArgumentProvider
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_ALLOW_VERSIONS = 0;
    final public const ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var Versions[]
     */
    private readonly array $defaultAllowVersions;
    /**
     * @var bool
     */
    private readonly bool $defaultAllowPrivateAndReserved;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param Versions[] $defaultAllowVersions
     * @param bool $defaultAllowPrivateAndReserved
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        array $defaultAllowVersions = [Versions::IPv4, Versions::IPv6],
        bool $defaultAllowPrivateAndReserved = true,
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

        $this->defaultAllowVersions = $defaultAllowVersions;
        $this->defaultAllowPrivateAndReserved = $defaultAllowPrivateAndReserved;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return Versions[]
     * @throws InvalidValidationArgumentsException
     */
    public function getAllowVersionsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): array {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ALLOW_VERSIONS,
            defaultValue: $this->defaultAllowVersions,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_iterable($argumentValue)) {
            throw new InvalidValidationArgumentsException(
                validatorName: IsIpAddressValidator::class,
                errors: [
                    sprintf(
                        'Allow Versions argument (%s) must be iterable; Received %s',
                        self::ARGUMENT_INDEX_ALLOW_VERSIONS,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        return array_map(
            static function (mixed $version) use ($arguments, $extractionPayload): Versions {
                switch (true) {
                    case $version instanceof Versions:
                        break;

                    case is_string($version):
                        try {
                            $version = Versions::from($version);
                        } catch (\TypeError | \ValueError) {
                            throw new InvalidValidationArgumentsException(
                                validatorName: IsIpAddressValidator::class,
                                errors: [
                                    sprintf(
                                        'Unrecognised Allow Versions argument (%s) value: %s',
                                        self::ARGUMENT_INDEX_ALLOW_VERSIONS,
                                        $version,
                                    ),
                                ],
                                arguments: $arguments,
                                data: $extractionPayload,
                            );
                        }
                        break;

                    default:
                        throw new InvalidValidationArgumentsException(
                            validatorName: IsIpAddressValidator::class,
                            errors: [
                                sprintf(
                                    'Allow Versions argument (%s) must be string|%s; Received %s',
                                    self::ARGUMENT_INDEX_ALLOW_VERSIONS,
                                    Versions::class,
                                    get_debug_type($version),
                                ),
                            ],
                            arguments: $arguments,
                            data: $extractionPayload,
                        );
                }

                return $version;
            },
            $this->convertIterableToArray($argumentValue),
        );
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return bool
     * @throws InvalidValidationArgumentsException
     */
    public function getAllowPrivateAndReservedArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED,
            defaultValue: $this->defaultAllowPrivateAndReserved,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidValidationArgumentsException(
                validatorName: IsIpAddressValidator::class,
                errors: [
                    sprintf(
                        'Allow Private and Reserved argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED,
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
