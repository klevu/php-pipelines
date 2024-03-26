<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider\Argument\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\ArgumentProvider;
use Klevu\Pipelines\Provider\ArgumentProviderInterface;
use Klevu\Pipelines\Transformer\StripTags as StripTagsTransformer;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class StripTagsArgumentProvider
{
    final public const ARGUMENT_INDEX_ALLOWED_TAGS = 0;
    final public const ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS = 1;

    /**
     * @var ArgumentProviderInterface
     */
    private readonly ArgumentProviderInterface $argumentProvider;
    /**
     * @var string[]|null
     */
    private readonly ?array $defaultAllowedTags;
    /**
     * @var string[]|null
     */
    private readonly ?array $defaultStripContentForTags;

    /**
     * @param ArgumentProviderInterface|null $argumentProvider
     * @param string[]|null $defaultAllowedTags
     * @param string[]|null $defaultStripContentForTags
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ArgumentProviderInterface $argumentProvider = null,
        ?array $defaultAllowedTags = null,
        ?array $defaultStripContentForTags = null,
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

        $this->defaultAllowedTags = (null !== $defaultAllowedTags)
            ? array_map(
                static fn (string $allowedTag): string => trim($allowedTag),
                $defaultAllowedTags,
            )
            : null;
        $this->defaultStripContentForTags = (null !== $defaultStripContentForTags)
            ? array_map(
                static fn (string $tag): string => trim($tag),
                $defaultStripContentForTags,
            )
            : null;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string[]|null
     * @throws InvalidTransformationArgumentsException
     */
    public function getAllowedTagsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?array {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_ALLOWED_TAGS,
            defaultValue: $this->defaultAllowedTags,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            return null;
        }

        if (!is_array($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: StripTagsTransformer::class,
                errors: [
                    sprintf(
                        'Allowed Tags argument (%s) must be null|array; Received %s',
                        self::ARGUMENT_INDEX_ALLOWED_TAGS,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        array_walk(
            $argumentValue,
            // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
            static function (mixed &$tag, int|string $index) use ($arguments, $extractionPayload): void {
                if (!is_string($tag)) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: StripTagsTransformer::class,
                        errors: [
                            sprintf(
                                'Allowed Tags argument (%s) items must be string; Received %s at index %s',
                                self::ARGUMENT_INDEX_ALLOWED_TAGS,
                                get_debug_type($tag),
                                $index,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                    );
                }

                $tag = trim($tag);
            },
        );

        return $argumentValue;
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return string[]|null
     * @throws InvalidTransformationArgumentsException
     */
    public function getStripContentForTagsArgumentValue(
        ?ArgumentIterator $arguments,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): ?array {
        $argumentValue = $this->argumentProvider->getArgumentValueWithExtractionExpansion(
            arguments: $arguments,
            argumentKey: self::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS,
            defaultValue: $this->defaultStripContentForTags,
            extractionPayload: $extractionPayload,
            extractionContext: $extractionContext,
        );

        if (null === $argumentValue) {
            return null;
        }

        if (!is_array($argumentValue)) {
            throw new InvalidTransformationArgumentsException(
                transformerName: StripTagsTransformer::class,
                errors: [
                    sprintf(
                        'Strip Content For Tags argument (%s) must be null|array; Received %s',
                        self::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS,
                        get_debug_type($argumentValue),
                    ),
                ],
                arguments: $arguments,
                data: $extractionPayload,
            );
        }

        array_walk(
            $argumentValue,
            // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
            static function (mixed &$tag, int|string $index) use ($arguments, $extractionPayload): void {
                if (!is_string($tag)) {
                    throw new InvalidTransformationArgumentsException(
                        transformerName: StripTagsTransformer::class,
                        errors: [
                            sprintf(
                                'Strip Content For Tags argument (%s) items must be string; Received %s at index %s',
                                self::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS,
                                get_debug_type($tag),
                                $index,
                            ),
                        ],
                        arguments: $arguments,
                        data: $extractionPayload,
                    );
                }

                $tag = trim($tag);
            },
        );

        return $argumentValue;
    }
}
