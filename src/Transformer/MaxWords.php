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
use Klevu\Pipelines\Provider\Argument\Transformer\MaxWordsArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to return up to the specified number of complete words from a string
 * Arguments:
 *  - <int> Maximum number of words
 *  - <string|null> Truncation string to append (optional)
 * @see MaxWordsArgumentProvider
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class MaxWords implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_MAX_WORDS = MaxWordsArgumentProvider::ARGUMENT_INDEX_MAX_WORDS;
    final public const ARGUMENT_INDEX_TRUNCATION_STRING = MaxWordsArgumentProvider::ARGUMENT_INDEX_TRUNCATION_STRING;

    /**
     * @var MaxWordsArgumentProvider
     */
    private readonly MaxWordsArgumentProvider $argumentProvider;

    /**
     * @param MaxWordsArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?MaxWordsArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(MaxWordsArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: MaxWordsArgumentProvider::class,
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

        $maxWordsArgumentValue = $this->argumentProvider->getMaxWordsArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $truncationStringArgumentValue = $this->argumentProvider->getTruncationStringArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $allWords = preg_split(
            pattern: '/\s/',
            subject: (string)$data,
            limit: $maxWordsArgumentValue + 1,
            flags: PREG_SPLIT_NO_EMPTY,
        );
        if (false === $allWords) {
            throw new TransformationException(
                transformerName: $this::class,
                errors: [
                    sprintf(
                        'Could not split string "%s" into individual words',
                        (string)$data,
                    ),
                ],
                arguments: $arguments,
                data: $data,
            );
        }

        $return = implode(
            ' ',
            array_slice($allWords, 0, $maxWordsArgumentValue),
        );

        if ($truncationStringArgumentValue && count($allWords) > $maxWordsArgumentValue) {
            $return .= $truncationStringArgumentValue;
        }

        return $return;
    }
}
