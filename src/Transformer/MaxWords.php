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
    final public const DEFAULT_MATCH_WORD_CHARACTERS = [
        '\w', // Standard "word" characters
        '\-', // For hyphenated words
        '\.', // For URLs
    ];

    /**
     * @var MaxWordsArgumentProvider
     */
    private readonly MaxWordsArgumentProvider $argumentProvider;
    /**
     * @var string[]
     */
    private readonly array $matchWordCharacters;

    /**
     * @param MaxWordsArgumentProvider|null $argumentProvider
     * @param string[] $matchWordCharacters
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?MaxWordsArgumentProvider $argumentProvider = null,
        array $matchWordCharacters = self::DEFAULT_MATCH_WORD_CHARACTERS,
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

        $this->matchWordCharacters = array_map(
            static fn (mixed $matchWordCharacter): string => match (true) {
                is_string($matchWordCharacter) => $matchWordCharacter,
                default => throw new \InvalidArgumentException(sprintf(
                    'Match Word Characters must be string; encountered %s',
                    get_debug_type($matchWordCharacter),
                )),
            },
            $matchWordCharacters,
        );
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

        $matchWordCharacters = implode('', $this->matchWordCharacters);
        $matches = [];
        preg_match_all(
            pattern: sprintf(
                '/(([^%s]*)([%s]+))/',
                $matchWordCharacters,
                $matchWordCharacters,
            ),
            subject: (string)$data,
            matches: $matches,
            flags: PREG_SET_ORDER,
        );

        $return = implode(
            separator: '',
            array: array_slice(
                array_column($matches, 0),
                0,
                $maxWordsArgumentValue,
            ),
        );

        if (count($matches) > $maxWordsArgumentValue) {
            $appendString = $truncationStringArgumentValue;
        } else {
            $matches = [];
            preg_match(
                pattern: sprintf('/([^%s]+)$/', $matchWordCharacters),
                subject: (string)$data,
                matches: $matches,
            );
            $appendString = $matches[0] ?? '';
        }

        return $return . $appendString;
    }
}
