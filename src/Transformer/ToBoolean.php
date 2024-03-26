<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\ToBooleanArgumentProvider;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ToBoolean implements TransformerInterface
{
    final public const ARGUMENT_INDEX_CASE_SENSITIVE = ToBooleanArgumentProvider::ARGUMENT_INDEX_CASE_SENSITIVE;
    final public const ARGUMENT_INDEX_TRIM_WHITESPACE = ToBooleanArgumentProvider::ARGUMENT_INDEX_TRIM_WHITESPACE;

    /**
     * @var ToBooleanArgumentProvider
     */
    private readonly ToBooleanArgumentProvider $argumentProvider;
    /**
     * @var mixed[]
     */
    private array $trueValuesList = [
        'true',
        1,
        '1',
        'yes',
    ];
    /**
     * @var mixed[]
     */
    private array $falseValuesList = [
        null,
        'false',
        0,
        '0',
        'no',
    ];

    /**
     * @param ToBooleanArgumentProvider|null $argumentProvider
     * @param mixed[]|null $trueValuesList
     * @param mixed[]|null $falseValuesList
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?ToBooleanArgumentProvider $argumentProvider,
        ?array $trueValuesList = null,
        ?array $falseValuesList = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(ToBooleanArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ToBooleanArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        if (null !== $trueValuesList) {
            $this->trueValuesList = $trueValuesList;
        }
        if (null !== $falseValuesList) {
            $this->falseValuesList = $falseValuesList;
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return bool
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): bool {
        if (is_bool($data)) {
            return $data;
        }

        $caseSensitiveArgumentValue = $this->argumentProvider->getCaseSensitiveArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        if ($caseSensitiveArgumentValue) {
            array_walk(
                array: $this->trueValuesList,
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                callback: static function (mixed &$trueValue): void {
                    if (is_string($trueValue)) {
                        $trueValue = strtolower($trueValue);
                    }
                },
            );
            array_walk(
                array: $this->falseValuesList,
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                callback: static function (mixed &$falseValue): void {
                    if (is_string($falseValue)) {
                        $falseValue = strtolower($falseValue);
                    }
                },
            );
        }
        $trimWhitespaceArgumentValue = $this->argumentProvider->getTrimWhitespaceArgumentValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $preparedData = $this->getPreparedData(
            data: $data,
            caseSensitive: $caseSensitiveArgumentValue,
            trimWhitespace: $trimWhitespaceArgumentValue,
        );

        return match (true) {
            in_array(
                needle: $preparedData,
                haystack: $this->falseValuesList,
                strict: true,
            ) => false,
            in_array(
                needle: $preparedData,
                haystack: $this->trueValuesList,
                strict: true,
            ) => true,
            default => (bool)$preparedData,
        };
    }

    /**
     * @param mixed $data
     * @param bool $caseSensitive
     * @param bool $trimWhitespace
     * @return mixed
     */
    private function getPreparedData(
        mixed $data,
        bool $caseSensitive,
        bool $trimWhitespace,
    ): mixed {
        $return = $data;

        if (is_string($return)) {
            if ($caseSensitive) {
                $return = strtolower($return);
            }

            if ($trimWhitespace) {
                $return = trim($return);
            }
        }

        return $return;
    }
}
