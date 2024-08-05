<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Transformer;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Handler\PropertyAccessHandler;
use Klevu\Pipelines\Handler\PropertyAccessHandlerInterface;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\SetPropertyValueTransformerArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class SetPropertyValue implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    // phpcs:disable Generic.Files.LineLength.TooLong
    final public const ARGUMENT_INDEX_PROPERTY_KEY = SetPropertyValueTransformerArgumentProvider::ARGUMENT_INDEX_PROPERTY_KEY;
    final public const ARGUMENT_INDEX_PROPERTY_VALUE = SetPropertyValueTransformerArgumentProvider::ARGUMENT_INDEX_PROPERTY_VALUE;
    final public const ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR = SetPropertyValueTransformerArgumentProvider::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR;
    final public const ARGUMENT_INDEX_ASSOCIATIVE = SetPropertyValueTransformerArgumentProvider::ARGUMENT_INDEX_ASSOCIATIVE;
    // phpcs:enable Generic.Files.LineLength.TooLong

    /**
     * @var SetPropertyValueTransformerArgumentProvider
     */
    private readonly SetPropertyValueTransformerArgumentProvider $argumentProvider;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;
    /**
     * @var PropertyAccessHandlerInterface
     */
    private readonly PropertyAccessHandlerInterface $propertyAccessHandler;

    /**
     * @param SetPropertyValueTransformerArgumentProvider|null $argumentProvider
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @param PropertyAccessHandlerInterface|null $propertyAccessHandler
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?SetPropertyValueTransformerArgumentProvider $argumentProvider = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
        ?PropertyAccessHandlerInterface $propertyAccessHandler = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(SetPropertyValueTransformerArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: SetPropertyValueTransformerArgumentProvider::class,
                instance: $argumentProvider,
            );
        }

        $argumentIteratorFactory ??= $container->get(ArgumentIteratorFactory::class);
        try {
            $this->argumentIteratorFactory = $argumentIteratorFactory; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentIteratorFactory::class,
                instance: $argumentProvider,
            );
        }

        $propertyAccessHandler ??= $container->get(PropertyAccessHandler::class);
        try {
            $this->propertyAccessHandler = $propertyAccessHandler; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: PropertyAccessHandler::class,
                instance: $propertyAccessHandler,
            );
        }
    }

    /**
     * @param mixed $data
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return mixed
     * @throws \Klevu\Pipelines\Exception\PropertyAccessExceptionInterface
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): mixed {
        $associative = $this->argumentProvider->getAssociateValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        if (null === $data) {
            $data = $associative
                ? []
                : (object)[];
        }

        if (!$this->propertyAccessHandler->isValidForPropertyAccess($data)) {
            throw new InvalidInputDataException(
                transformerName: $this::class,
                expectedType: 'object|array',
                arguments: $arguments,
                data: $data,
            );
        }

        $propertyKey = $this->argumentProvider->getPropertyKeyValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );
        $propertyPathSeparator = $this->argumentProvider->getPropertyPathSeparatorValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        $propertyPathExploded = explode(
            separator: $propertyPathSeparator,
            string: $propertyKey,
        );
        $currentPropertyKey = array_shift($propertyPathExploded);

        if (in_array($currentPropertyKey, [null, ''], true)) {
            return $data;
        }

        // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
        /** @var object|mixed[] $data */
        if ($propertyPathExploded) {
            if (!$this->propertyAccessHandler->propertyExists($data, $currentPropertyKey)) {
                $data = $this->propertyAccessHandler->setPropertyValue(
                    subject: $data,
                    propertyKey: $currentPropertyKey,
                    propertyValue: ($associative) ? [] : (object)[],
                );
            }
            $existingPropertyValue = $this->propertyAccessHandler->getPropertyValue(
                subject: $data,
                propertyKey: $currentPropertyKey,
            );

            $arguments ??= new ArgumentIterator();
            $newPropertyValue = $this->transform(
                data: $existingPropertyValue,
                arguments: $arguments->mergeByArgumentKey(
                    iterator: $this->argumentIteratorFactory->create([
                        new Argument(
                            value: implode(
                                separator: $propertyPathSeparator,
                                array: $propertyPathExploded,
                            ),
                            key: self::ARGUMENT_INDEX_PROPERTY_KEY,
                        ),
                    ]),
                ),
                context: $context,
            );

            return $this->propertyAccessHandler->setPropertyValue(
                subject: $data,
                propertyKey: $currentPropertyKey,
                propertyValue: $newPropertyValue,
            );
        }

        $propertyValue = $this->argumentProvider->getPropertyValueValue(
            arguments: $arguments,
            extractionPayload: $data,
            extractionContext: $context,
        );

        return $this->propertyAccessHandler->setPropertyValue(
            subject: $data,
            propertyKey: $currentPropertyKey,
            propertyValue: $propertyValue,
        );
    }
}
