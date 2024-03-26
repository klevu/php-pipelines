<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Stage;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\ObjectManagerException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\TransformationExceptionInterface;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\Model\TransformationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\TransformerManager;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesNotSupportedTrait;
use Klevu\Pipelines\Transformer\TransformerInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Transform implements PipelineInterface
{
    use StagesNotSupportedTrait;

    public const ARGUMENT_KEY_TRANSFORMATION = 'transformation';

    /**
     * @var TransformerManager
     */
    private readonly TransformerManager $transformerManager;
    /**
     * @var TransformationIteratorFactory
     */
    private readonly TransformationIteratorFactory $transformationIteratorFactory;
    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var TransformationIterator
     */
    private TransformationIterator $transformations;

    /**
     * @param TransformerManager|null $transformerManager
     * @param TransformationIteratorFactory|null $transformationIteratorFactory
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     */
    public function __construct(
        ?TransformerManager $transformerManager = null,
        ?TransformationIteratorFactory $transformationIteratorFactory = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        if (null === $transformerManager) {
            $transformerManager = $container->get(TransformerManager::class);
            if (!($transformerManager instanceof TransformerManager)) {
                throw new InvalidClassException(
                    identifier: TransformerManager::class,
                    instance: $transformerManager,
                );
            }
        }
        $this->transformerManager = $transformerManager;

        if (null === $transformationIteratorFactory) {
            $transformationIteratorFactory = $container->get(TransformationIteratorFactory::class);
            if (!($transformationIteratorFactory instanceof TransformationIteratorFactory)) {
                throw new InvalidClassException(
                    identifier: TransformationIteratorFactory::class,
                    instance: $transformationIteratorFactory,
                );
            }
        }
        $this->transformationIteratorFactory = $transformationIteratorFactory;

        $this->transformations = new TransformationIterator([]);

        array_walk($stages, [$this, 'addStage']);
        if ($args) {
            $this->setArgs($args);
        }

        $this->identifier = $identifier;
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @param mixed[] $args
     * @return void
     */
    public function setArgs(array $args): void
    {
        if (!($args[static::ARGUMENT_KEY_TRANSFORMATION] ?? null)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $args,
                message: sprintf(
                    'Transformation argument (%s) is required',
                    static::ARGUMENT_KEY_TRANSFORMATION,
                ),
            );
        }

        $this->transformations = $this->prepareTransformationArgument(
            transformation: $args[static::ARGUMENT_KEY_TRANSFORMATION],
            arguments: $args,
        );
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ObjectManagerException
     * @throws TransformationException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        $return = (is_object($payload) && !($payload instanceof \Generator))
            ? clone $payload
            : $payload;

        /** @var Transformation $transformation */
        foreach ($this->transformations as $transformation) {
            /** @var TransformerInterface $transformer */
            $transformer = $this->transformerManager->get(
                $transformation->transformerName,
            );

            try {
                $return = $transformer->transform(
                    data: $return,
                    arguments: $transformation->arguments,
                    context: $context,
                );
            } catch (TransformationExceptionInterface $exception) {
                throw new StageException(
                    pipeline: $this,
                    previous: $exception,
                );
            }
        }

        return $return;
    }

    /**
     * @param mixed $transformation
     * @param mixed[]|null $arguments
     * @return TransformationIterator
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareTransformationArgument(
        mixed $transformation,
        ?array $arguments,
    ): TransformationIterator {
        if (is_array($transformation)) {
            $return = new TransformationIterator();
            foreach ($transformation as $transformationItem) {
                $return->merge(
                    $this->prepareTransformationArgument($transformationItem, $arguments),
                );
            }

            return $return;
        }

        if ($transformation instanceof TransformationIterator) {
            return $transformation;
        }

        if ($transformation instanceof Transformation) {
            return new TransformationIterator([$transformation]);
        }

        if (!is_string($transformation)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Transformation argument (%s) must be instance of %s or transformation string; Received %s',
                    static::ARGUMENT_KEY_TRANSFORMATION,
                    $this::class,
                    get_debug_type($transformation),
                ),
            );
        }

        $transformations = $this->transformationIteratorFactory->createFromSyntaxDeclaration(
            syntaxDeclaration: trim($transformation),
        );
        if (!count($transformations)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Transformation argument (%s) does not contain any valid transformation strings',
                    static::ARGUMENT_KEY_TRANSFORMATION,
                ),
            );
        }

        return $transformations;
    }
}
