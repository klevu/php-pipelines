<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Provider;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class ArgumentProvider implements ArgumentProviderInterface
{
    use ConvertIterableToArrayTrait;

    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;

    /**
     * @param Extractor|null $extractor
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?Extractor $extractor = null,
    ) {
        $container = Container::getInstance();

        $extractor ??= $container->get(Extractor::class);
        try {
            $this->extractor = $extractor; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: Extractor::class,
                instance: $extractor,
            );
        }
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param int|string $argumentKey
     * @param mixed|null $defaultValue
     * @return mixed
     */
    public function getArgumentValue(
        ?ArgumentIterator $arguments,
        int|string $argumentKey,
        mixed $defaultValue = null,
    ): mixed {
        if (null === $arguments) {
            $arguments = new ArgumentIterator();
        }

        $argument = $arguments->getByKey($argumentKey)
            ?? new Argument(
                value: $defaultValue,
                key: $argumentKey,
            );

        return $argument->getValue();
    }

    /**
     * @param ArgumentIterator|null $arguments
     * @param int|string $argumentKey
     * @param mixed|null $defaultValue
     * @param mixed|null $extractionPayload
     * @param \ArrayAccess<string|int, mixed>|null $extractionContext
     * @return mixed
     */
    public function getArgumentValueWithExtractionExpansion(
        ?ArgumentIterator $arguments,
        int|string $argumentKey,
        mixed $defaultValue = null,
        mixed $extractionPayload = null,
        ?\ArrayAccess $extractionContext = null,
    ): mixed {
        $argumentValue = $this->getArgumentValue(
            arguments: $arguments,
            argumentKey: $argumentKey,
            defaultValue: $defaultValue,
        );

        switch (true) {
            case $argumentValue instanceof Extraction:
                $argumentValue = $this->extractor->extract(
                    source: $extractionPayload,
                    accessor: $argumentValue->accessor,
                    context: $extractionContext,
                );
                break;

            case $argumentValue instanceof ArgumentIterator:
                $return = [];
                /** @var Argument $childArgument */
                foreach ($argumentValue as $childArgument) {
                    $childArgumentKey = $childArgument->getKey();
                    if ($childArgumentKey instanceof Extraction) {
                        $childArgumentKey = $this->extractor->extract(
                            source: $extractionPayload,
                            accessor: $childArgumentKey->accessor,
                            context: $extractionContext,
                        );
                    }

                    $childArgumentValue = $childArgument->getValue();
                    if ($childArgumentValue instanceof Extraction) {
                        $childArgumentValue = $this->extractor->extract(
                            source: $extractionPayload,
                            accessor: $childArgumentValue->accessor,
                            context: $extractionContext,
                        );
                    }

                    $return[$childArgumentKey] = $childArgumentValue;
                }

                $argumentValue = $return;
                break;
        }

        return $argumentValue;
    }
}
