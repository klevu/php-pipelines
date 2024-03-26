<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Extractor;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\ExtractionExceptionInterface;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationExceptionInterface;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Parser\ArgumentConverter;
use Klevu\Pipelines\Pipeline\ConfigurationElements;
use Klevu\Pipelines\Pipeline\PipelineBuilder;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Extractor
{
    final public const ACCESSOR_CHAIN_SEPARATOR = '.';
    final public const ACCESSOR_CONTEXT_SEPARATOR = '::';

    /**
     * @var PipelineBuilder
     */
    private readonly PipelineBuilder $pipelineBuilder;
    /**
     * @var ArgumentConverter
     */
    private readonly ArgumentConverter $argumentConverter;

    /**
     * @param PipelineBuilder|null $pipelineBuilder
     * @param ArgumentConverter|null $argumentConverter
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    public function __construct(
        ?PipelineBuilder $pipelineBuilder = null,
        ?ArgumentConverter $argumentConverter = null,
    ) {
        $container = Container::getInstance();

        $pipelineBuilder ??= $container->get(PipelineBuilder::class);
        try {
            $this->pipelineBuilder = $pipelineBuilder; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: PipelineBuilder::class,
                instance: $pipelineBuilder,
            );
        }

        $argumentConverter ??= $container->get(ArgumentConverter::class);
        try {
            $this->argumentConverter = $argumentConverter; // @phpstan-ignore-line (we catch TypeError)
        } catch (\TypeError) {
            throw new InvalidClassException(
                identifier: ArgumentConverter::class,
                instance: $argumentConverter,
            );
        }
    }

    /**
     * @param mixed $source
     * @param string|Extraction|null $accessor
     * @param TransformationIterator|null $transformations
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ExtractionExceptionInterface
     * @throws TransformationExceptionInterface
     */
    public function extract(
        mixed $source,
        string|Extraction|null $accessor,
        ?TransformationIterator $transformations = null,
        ?\ArrayAccess $context = null,
    ): mixed {
        $extractedValue = $this->performExtraction(
            source: $source,
            accessor: $accessor,
            context: $context,
        );

        return $this->performTransformations(
            source: $extractedValue,
            transformations: $transformations,
            context: $context,
        );
    }

    /**
     * @param mixed $source
     * @param string|null $accessor
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ExtractionExceptionInterface
     */
    private function performExtraction(
        mixed $source,
        string|Extraction|null $accessor,
        ?\ArrayAccess $context = null,
    ): mixed {
        if ($accessor instanceof Extraction) {
            $accessor = $this->performExtraction(
                source: $source,
                accessor: $accessor->accessor,
                context: $context,
            );

            if (null !== $accessor && !is_string($accessor)) {
                throw new ExtractionException(
                    message: sprintf(
                        'Could not extract using accessor "%s" on %s',
                        is_scalar($accessor) ? $accessor : get_debug_type($accessor),
                        get_debug_type($source),
                    ),
                );
            }
        }

        $accessorChain = $this->splitAccessorChain($accessor);
        $accessorIsEmpty = !$accessor
            || !array_filter($accessorChain, static fn (?string $accessor): bool => !!trim((string)$accessor));

        if ($accessorIsEmpty) {
            return $source;
        }

        if (count($accessorChain) > 1) {
            $newAccessor = array_shift($accessorChain);

            return $this->extract(
                source: $this->extract(
                    source: $source,
                    accessor: $newAccessor,
                    context: $context,
                ),
                accessor: implode(self::ACCESSOR_CHAIN_SEPARATOR, $accessorChain),
                context: $context,
            );
        }

        if (str_contains($accessor, self::ACCESSOR_CONTEXT_SEPARATOR)) {
            [$sourceKey, $accessor] = explode(self::ACCESSOR_CONTEXT_SEPARATOR, $accessor);
            $source = $context[$sourceKey] ?? null;

            if ('' === $accessor) {
                return $source;
            }
        }

        try {
            switch (true) {
                case str_ends_with($accessor, '()') && (is_iterable($source) || is_object($source)):
                    $accessorMethod = substr($accessor, 0, -2);

                    if (is_iterable($source)) {
                        $return = [];
                        foreach ($source as $sourceKey => $sourceItem) {
                            $return[$sourceKey] = $this->executeAccessorMethod($sourceItem, $accessorMethod);
                        }
                    } else {
                        $return = $this->executeAccessorMethod($source, $accessorMethod);
                    }
                    break;

                case is_array($source):
                    if (!array_key_exists($accessor, $source)) {
                        throw new ExtractionException(
                            message: sprintf(
                                'Property "%s" is not publicly available on %s',
                                $accessor,
                                get_debug_type($source),
                            ),
                        );
                    }

                    $return = $source[$accessor];
                    break;

                case is_object($source):
                    if (!property_exists($source, $accessor) && !method_exists($source, '__get')) {
                        throw new ExtractionException(
                            message: sprintf(
                                'Property "%s" is not publicly available on %s',
                                $accessor,
                                get_debug_type($source),
                            ),
                        );
                    }

                    $return = $source->{$accessor};
                    break;

                default:
                    throw new ExtractionException(
                        message: sprintf(
                            'Cannot perform property extraction (%s) on items of type %s',
                            $accessor,
                            get_debug_type($source),
                        ),
                    );
            }
        } catch (\Error $exception) {
            throw new ExtractionException(
                message: sprintf('Could not extract using accessor "%s" on %s', $accessor, get_debug_type($source)),
                code: $exception->getCode(),
                previous: $exception,
            );
        }

        return $return;
    }

    /**
     * @param mixed $source
     * @param TransformationIterator|null $transformations
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws \Throwable
     */
    private function performTransformations(
        mixed $source,
        ?TransformationIterator $transformations,
        ?\ArrayAccess $context = null,
    ): mixed {
        if (!$transformations?->count()) {
            return $source;
        }

        $transformPipelineConfiguration = [
            ConfigurationElements::STAGES->value => array_map(
                static fn (Transformation $transformation): array => [
                    ConfigurationElements::PIPELINE->value => 'Stage\Transform',
                    ConfigurationElements::ARGS->value => [
                        'transformation' => $transformation,
                    ],
                ],
                $transformations->toArray(),
            ),
        ];
        $transformPipeline = $this->pipelineBuilder->build($transformPipelineConfiguration);

        try {
            return $transformPipeline->execute(
                payload: $source,
                context: $context,
            );
        } catch (StageException $exception) {
            throw $exception->getPrevious() ?: $exception;
        }
    }

    /**
     * @param string|null $accessorChain
     * @return string[]
     */
    private function splitAccessorChain(?string $accessorChain): array
    {
        return array_map(
            'trim',
            explode(self::ACCESSOR_CHAIN_SEPARATOR, $accessorChain ?? ''),
        );
    }

    /**
     * @param object $source
     * @param string $accessorMethod
     * @return mixed
     */
    private function executeAccessorMethod(object $source, string $accessorMethod): mixed
    {
        if (!method_exists($source, $accessorMethod) && !method_exists($source, '__call')) {
            throw new ExtractionException(
                message: sprintf(
                    'Method "%s" is not publicly available on source of type %s',
                    $accessorMethod,
                    get_debug_type($source),
                ),
            );
        }

        return $source->{$accessorMethod}();
    }
}
