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
use Klevu\Pipelines\Provider\Argument\Validator\IsUrlArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * @todo Implement multibyte support for non-latin URLs
 */
class IsUrl implements ValidatorInterface
{
    final public const ARGUMENT_INDEX_REQUIRE_PROTOCOL = IsUrlArgumentProvider::ARGUMENT_INDEX_REQUIRE_PROTOCOL;

    /**
     * @var IsUrlArgumentProvider
     */
    private readonly IsUrlArgumentProvider $argumentProvider;
    /**
     * @var string[]
     */
    private array $supportedProtocols = [
        'http',
        'https',
        'ftp',
        'ftps',
        'sftp',
        'tcp',
        'ssh',
    ];
    /**
     * @var bool
     */
    private readonly bool $allowAuthorization;

    /**
     * @param IsUrlArgumentProvider|null $argumentProvider
     * @param string[]|null $supportedProtocols
     * @param bool $allowAuthorization
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?IsUrlArgumentProvider $argumentProvider = null,
        ?array $supportedProtocols = null,
        bool $allowAuthorization = false,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(IsUrlArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: IsUrlArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        if (null !== $supportedProtocols) {
            $this->supportedProtocols = array_map('strval', $supportedProtocols);
        }
        $this->allowAuthorization = $allowAuthorization;
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

        $requireProtocolArgumentValue = $this->argumentProvider->getRequireProtocolArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $dataForFilterVar = (!$requireProtocolArgumentValue && !preg_match('#^[a-zA-Z]+://#', $data))
            ? 'https://' . ltrim($data, '/')
            : preg_replace('#^//#', 'https://', $data);

        $matchesRegex = preg_match(
            pattern: $this->getRegularExpression(),
            subject: $data,
        );
        $passesFilterVar = filter_var($dataForFilterVar, FILTER_VALIDATE_URL);

        if (!$matchesRegex || !$passesFilterVar) {
            throw new InvalidDataValidationException(
                validatorName: $this::class,
                errors: [
                    sprintf(
                        'Data must be valid URL; Received %s',
                        $data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }
    }

    /**
     * @return string
     */
    private function getRegularExpression(): string
    {
        $protocolPatternParts = array_map(
            callback: static fn (string $supportedProtocol): string => ('' === $supportedProtocol)
                ? '\/\/'
                : preg_quote($supportedProtocol) . ':\/\/',
            array: $this->supportedProtocols,
        );
        $protocolPattern = ($protocolPatternParts)
            ? '(' . implode('|', $protocolPatternParts) . ')?'
            : '';

        $authorizationPattern = ($this->allowAuthorization)
            ? '(([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+' // username
                . '(:([a-z0-9$_\.\+!\*\'\(\),;\?&=-]|%[0-9a-f]{2})+)?' // password
                . '@)?'
            : '';

        $domainPattern = '(([a-z0-9]\.|[a-z0-9][a-z0-9-]*[a-z0-9]\.)+[a-z][a-z0-9-]*[a-z0-9])';
        $portPattern = '(:\d+)?';
        $pathPattern = '(\/+([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)*';
        $querystringPattern = '(\?([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?';
        $fragmentPattern = '(#([a-z0-9$_\.\+!\*\'\(\),;:@&=-]|%[0-9a-f]{2})*)?';

        return str_replace(
            search: [
                '{protocol}',
                '{authorization}',
                '{domain}',
                '{port}',
                '{path}',
                '{querystring}',
                '{fragment}',
            ],
            replace: [
                $protocolPattern,
                $authorizationPattern,
                $domainPattern,
                $portPattern,
                $pathPattern,
                $querystringPattern,
                $fragmentPattern,
            ],
            subject: '`^{protocol}{authorization}{domain}{port}{path}{querystring}{fragment}$`i',
        );
    }
}
