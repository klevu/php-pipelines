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
use Klevu\Pipelines\Provider\Argument\Transformer\StripTagsArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to strip HTML tags from string.
 *  - <null|array<string>|Extraction> Allowed Tags (eg p, strong)
 *  - <null|array<string>|Extraction> Tags for which to strip entire content (eg script, style)
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class StripTags implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_ALLOWED_TAGS = StripTagsArgumentProvider::ARGUMENT_INDEX_ALLOWED_TAGS;
    final public const ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS = StripTagsArgumentProvider::ARGUMENT_INDEX_STRIP_CONTENT_FOR_TAGS; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var StripTagsArgumentProvider
     */
    private readonly StripTagsArgumentProvider $argumentProvider;

    /**
     * @param StripTagsArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?StripTagsArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(StripTagsArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: StripTagsArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|array<string|null>|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): null|string|array {
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

        $data = (string)$data;

        $allowedTagsArgumentValue = $this->argumentProvider->getAllowedTagsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $stripContentForTagsArgumentValue = $this->argumentProvider->getStripContentForTagsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        if ($stripContentForTagsArgumentValue) {
            $data = preg_replace( // phpcs:ignore Security.BadFunctions.PregReplace.PregReplaceDyn
                pattern: sprintf(
                    '#<((%s)[^>]*)>(.|\s)*?</\2\s*>#im',
                    implode('|', array_map('preg_quote', $stripContentForTagsArgumentValue)),
                ),
                replacement: '<\\1></\\1>',
                subject: $data,
            );

            if (null === $data) {
                throw new TransformationException(
                    transformerName: $this::class,
                    errors: [
                        sprintf(
                            'An error occurred while stripping content for tags %s',
                            json_encode($stripContentForTagsArgumentValue),
                        ),
                    ],
                    arguments: $arguments,
                    data: $data,
                );
            }
        }

        return strip_tags(
            string: $data,
            allowed_tags: $allowedTagsArgumentValue,
        );
    }
}
