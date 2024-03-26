<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Transformation\StringPositions;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\TrimArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

/**
 * Transformer to trim whitespace and (optionally) other characters from start and/or end of input data
 * Arguments
 *  - <null|string|Extraction> Characters
 *  - <null|StringPositions|string> Position
 *
 * @method ?string performRecursiveCall(array $data, ?ArgumentIterator $arguments, ?\ArrayAccess $context))
 */
class Trim implements TransformerInterface
{
    use RecursiveCallTrait;

    final public const ARGUMENT_INDEX_CHARACTERS = TrimArgumentProvider::ARGUMENT_INDEX_CHARACTERS;
    final public const ARGUMENT_INDEX_POSITION = TrimArgumentProvider::ARGUMENT_INDEX_POSITION;

    /**
     * @var TrimArgumentProvider
     */
    private readonly TrimArgumentProvider $argumentProvider;

    /**
     * @param TrimArgumentProvider|null $argumentProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?TrimArgumentProvider $argumentProvider = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(TrimArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: TrimArgumentProvider::class,
                instance: $argumentProvider,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return string|array<string|null>|null
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

        if (!is_string($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'null|string|string[]',
                arguments: $arguments,
                data: $data,
            );
        }

        $charactersArgumentValue = $this->argumentProvider->getCharactersArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $positionArgumentValue = $this->argumentProvider->getPositionArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return match ($positionArgumentValue) {
            StringPositions::START => ltrim($data, $charactersArgumentValue),
            StringPositions::END => rtrim($data, $charactersArgumentValue),
            StringPositions::BOTH => trim($data, $charactersArgumentValue),
        };
    }
}
