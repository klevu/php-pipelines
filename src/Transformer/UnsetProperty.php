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
use Klevu\Pipelines\Handler\PropertyAccessHandler;
use Klevu\Pipelines\Handler\PropertyAccessHandlerInterface;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Provider\Argument\Transformer\UnsetPropertyArgumentProvider;
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class UnsetProperty implements TransformerInterface
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_INDEX_PROPERTY_KEY = UnsetPropertyArgumentProvider::ARGUMENT_INDEX_PROPERTY_KEY;
    final public const ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR = UnsetPropertyArgumentProvider::ARGUMENT_INDEX_PROPERTY_PATH_SEPARATOR; // phpcs:ignore Generic.Files.LineLength.TooLong

    /**
     * @var UnsetPropertyArgumentProvider
     */
    private readonly UnsetPropertyArgumentProvider $argumentProvider;
    /**
     * @var ArgumentIteratorFactory
     */
    private readonly ArgumentIteratorFactory $argumentIteratorFactory;
    /**
     * @var PropertyAccessHandlerInterface
     */
    private readonly PropertyAccessHandlerInterface $propertyAccessHandler;

    /**
     * @param UnsetPropertyArgumentProvider|null $argumentProvider
     * @param ArgumentIteratorFactory|null $argumentIteratorFactory
     * @param PropertyAccessHandlerInterface|null $propertyAccessHandler
     *
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?UnsetPropertyArgumentProvider $argumentProvider = null,
        ?ArgumentIteratorFactory $argumentIteratorFactory = null,
        ?PropertyAccessHandlerInterface $propertyAccessHandler = null,
    ) {
        $container = Container::getInstance();

        $argumentProvider ??= $container->get(UnsetPropertyArgumentProvider::class);
        try {
            $this->argumentProvider = $argumentProvider; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: UnsetPropertyArgumentProvider::class,
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
     * @return object|mixed[]|null
     * @throws TransformationException
     * @throws InvalidInputDataException
     * @throws InvalidTransformationArgumentsException
     */
    public function transform(
        mixed $data,
        ?ArgumentIterator $arguments = null,
        ?\ArrayAccess $context = null,
    ): object|array|null {
        if (null === $data) {
            return null;
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

        // phpcs:ignore SlevomatCodingStandard.Commenting.InlineDocCommentDeclaration.MissingVariable
        /** @var object|mixed[] $data */
        if (
            in_array($currentPropertyKey, [null, ''], true)
            || !$this->propertyAccessHandler->propertyExists($data, $currentPropertyKey)
        ) {
            return $data;
        }

        if ($propertyPathExploded) { // We still have keys to process further down the path
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

        return $this->propertyAccessHandler->unsetProperty(
            subject: $data,
            propertyKey: $currentPropertyKey,
        );
    }
}
