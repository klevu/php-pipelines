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
use Klevu\Pipelines\Model\Validation\IsIpAddress\Versions;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Validator\IsIpAddressArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class IsIpAddress implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_ALLOW_VERSIONS = IsIpAddressArgumentProvider::ARGUMENT_INDEX_ALLOW_VERSIONS;
    final public const ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED = IsIpAddressArgumentProvider::ARGUMENT_INDEX_ALLOW_PRIVATE_AND_RESERVED; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var IsIpAddressArgumentProvider
     */
    private IsIpAddressArgumentProvider $argumentProvider;

    /**
     * @param IsIpAddressArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsIpAddressArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsIpAddressArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsIpAddressArgumentProvider::class,
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
        ?\ArrayAccess $context = null,
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

        $allowVersionsArgumentValue = $this->argumentProvider->getAllowVersionsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $allowPrivateAndReservedArgumentValue = $this->argumentProvider->getAllowPrivateAndReservedArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $filterFlags = 0;
        if (in_array(Versions::IPv4, $allowVersionsArgumentValue, true)) {
            $filterFlags |= FILTER_FLAG_IPV4;
        }
        if (in_array(Versions::IPv6, $allowVersionsArgumentValue, true)) {
            $filterFlags |= FILTER_FLAG_IPV6;
        }
        if (!$allowPrivateAndReservedArgumentValue) {
            $filterFlags |= FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE;
        }

        $isValid = filter_var(
            value: $data,
            filter: FILTER_VALIDATE_IP,
            options: $filterFlags,
        );

        if (!$isValid) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Value "%s" is not a valid IP address',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }
}
