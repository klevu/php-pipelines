<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\EscapeHtml\Quotes;
use Klevu\Pipelines\Model\Transformation\EscapeHtml\TranslationTables;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\EscapeHtml as EscapeHtmlTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class EscapeHtmlArgumentProvider
{
    final public const ARGUMENT_INDEX_QUOTES = 0;
    final public const ARGUMENT_INDEX_TRANSLATION_TABLE = 1;
    final public const ARGUMENT_ALLOW_DOUBLE_ENCODING = 2;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var Quotes
     */
    private readonly Quotes $defaultQuotes;
    /**
     * @var TranslationTables
     */
    private readonly TranslationTables $defaultTranslationTable;
    /**
     * @var bool
     */
    private readonly bool $defaultAllowDoubleEncoding;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param Quotes|null $defaultQuotes
     * @param TranslationTables|null $defaultTranslationTable
     * @param bool|null $defaultAllowDoubleEncoding
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?Quotes $defaultQuotes = null,
        ?TranslationTables $defaultTranslationTable = null,
        ?bool $defaultAllowDoubleEncoding = null,
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

        $this->defaultQuotes = $defaultQuotes ?? Quotes::COMPAT;
        $this->defaultTranslationTable = $defaultTranslationTable ?? TranslationTables::XML1;
        $this->defaultAllowDoubleEncoding = $defaultAllowDoubleEncoding ?? false;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return Quotes
     * @throws InvalidTransformationArgumentsException
     */
    public function getQuotesArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): Quotes {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_QUOTES,
            defaultValue: $this->defaultQuotes,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case $argumentValue instanceof Quotes:
                break;

            case null === $argumentValue:
                $argumentValue = $this->defaultQuotes;
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = Quotes::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: EscapeHtmlTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Quotes argument (%s) value',
                                self::ARGUMENT_INDEX_QUOTES,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                        previous: $exception,
                    );
                }
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: EscapeHtmlTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Quotes argument (%s) : %s',
                            self::ARGUMENT_INDEX_QUOTES,
                            is_scalar($argumentValue)
                                ? $argumentValue
                                : get_debug_type($argumentValue),
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
     * @return TranslationTables
     * @throws InvalidTransformationArgumentsException
     */
    public function getTranslationTableArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): TranslationTables {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_TRANSLATION_TABLE,
            defaultValue: $this->defaultTranslationTable,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        switch (true) {
            case $argumentValue instanceof TranslationTables:
                break;

            case null === $argumentValue:
                $argumentValue = $this->defaultTranslationTable;
                break;

            case is_string($argumentValue):
                try {
                    $argumentValue = TranslationTables::from($argumentValue);
                } catch (\ValueError $exception) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: EscapeHtmlTransformer::class,
                        errors: [
                            sprintf(
                                'Unrecognised Translation Table argument (%s) value',
                                self::ARGUMENT_INDEX_TRANSLATION_TABLE,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                        previous: $exception,
                    );
                }
                break;

            default:
                throw new InvalidTransformationArgumentsException(
                    transformerName: EscapeHtmlTransformer::class,
                    errors: [
                        sprintf(
                            'Invalid Translation Table argument (%s) : %s',
                            self::ARGUMENT_INDEX_TRANSLATION_TABLE,
                            is_scalar($argumentValue)
                                ? $argumentValue
                                : get_debug_type($argumentValue),
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
     * @throws InvalidTransformationArgumentsException
     */
    public function getAllowDoubleEncodingArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): bool {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_ALLOW_DOUBLE_ENCODING,
            defaultValue: $this->defaultAllowDoubleEncoding,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (!is_bool($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: EscapeHtmlTransformer::class,
                errors: [
                    sprintf(
                        'Allow Double Encoding argument (%s) must be boolean; Received %s',
                        self::ARGUMENT_ALLOW_DOUBLE_ENCODING,
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
