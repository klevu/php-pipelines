<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\EscapeHtmlArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to escape HTML reserved characters
 *  - <null|Quotes> Mode used to convert quote characters (' and ")
 *  - <null|TranslationTables> Entity translation table used
 *  - <null|bool> Allow double encoding
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class EscapeHtml implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_QUOTES = EscapeHtmlArgumentProvider::ARGUMENT_INDEX_QUOTES;
    final public const ARGUMENT_INDEX_TRANSLATION_TABLE = EscapeHtmlArgumentProvider::ARGUMENT_INDEX_TRANSLATION_TABLE;
    final public const ARGUMENT_ALLOW_DOUBLE_ENCODING = EscapeHtmlArgumentProvider::ARGUMENT_ALLOW_DOUBLE_ENCODING;

    /**
     * @var EscapeHtmlArgumentProvider
     */
    private readonly EscapeHtmlArgumentProvider $argumentProvider;

    /**
     * @param EscapeHtmlArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?EscapeHtmlArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(EscapeHtmlArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: EscapeHtmlArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return array<string|null>|string|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): array|string|null {
        if (null === $data) {
            return null;
        }

        if ($this->shouldCallRecursively($data)) {
            return $this->performRecursiveCall(
                data: (array)$data,
                arguments: $arguments,
                context: $context,
            );
        }

        if (!is_scalar($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'scalar|iterable',
                arguments: $arguments,
                data: $data,
            );
        }

        $quotesArgumentValue = $this->argumentProvider->getQuotesArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $translationTableArgumentValue = $this->argumentProvider->getTranslationTableArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $allowDoubleEncodingArgumentValue = $this->argumentProvider->getAllowDoubleEncodingArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return htmlentities(
            string: (string)$data,
            flags: ENT_SUBSTITUTE
                | $quotesArgumentValue->htmlentitiesFlag()
                | $translationTableArgumentValue->htmlentitiesFlag(),
            double_encode: $allowDoubleEncodingArgumentValue,
        );
    }
}
