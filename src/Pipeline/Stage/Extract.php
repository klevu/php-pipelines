<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Stage;

use Klevu\Pipelines\Exception\ExtractionExceptionInterface;
use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationExceptionInterface;
use Klevu\Pipelines\Extractor\Extractor;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\Model\TransformationIteratorFactory;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\Parser\ArgumentConverter;
use Klevu\Pipelines\Parser\SyntaxParser;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesNotSupportedTrait;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class Extract implements PipelineInterface
{
    use StagesNotSupportedTrait;

    final public const ARGUMENT_KEY_EXTRACTION = 'extraction';
    final public const ARGUMENT_KEY_TRANSFORMATIONS = 'transformations';
    final public const ARGUMENT_KEY_TOLERATE_EXTRACTION_EXCEPTION = 'tolerateExtractionException';
    final public const ARGUMENT_KEY_TOLERATE_TRANSFORMATION_EXCEPTION = 'tolerateTransformationException';

    /**
     * @var string
     */
    private readonly string $identifier;
    /**
     * @var Extractor
     */
    private readonly Extractor $extractor;
    /**
     * @var ArgumentConverter
     */
    private readonly ArgumentConverter $argumentConverter;
    /**
     * @var TransformationIteratorFactory
     */
    private readonly TransformationIteratorFactory $transformationIteratorFactory;
    /**
     * @var Extraction|null
     */
    private ?Extraction $extraction = null;
    /**
     * @var class-string[]
     */
    private array $tolerateExceptionFqcns = [];

    /**
     * @param Extractor|null $extractor
     * @param ArgumentConverter|null $argumentConverter
     * @param TransformationIteratorFactory|null $transformationIteratorFactory
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     * @throws InvalidPipelineArgumentsException
     */
    public function __construct(
        ?Extractor $extractor = null,
        ?ArgumentConverter $argumentConverter = null,
        ?TransformationIteratorFactory $transformationIteratorFactory = null,
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
        $container = Container::getInstance();

        if (null === $extractor) {
            $extractor = $container->get(Extractor::class);
            if (!($extractor instanceof Extractor)) {
                throw new InvalidClassException(
                    identifier: Extractor::class,
                    instance: $extractor,
                );
            }
        }
        $this->extractor = $extractor;

        if (null === $argumentConverter) {
            $argumentConverter = $container->get(ArgumentConverter::class);
            if (!($argumentConverter instanceof ArgumentConverter)) {
                throw new InvalidClassException(
                    identifier: ArgumentConverter::class,
                    instance: $argumentConverter,
                );
            }
        }
        $this->argumentConverter = $argumentConverter;

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
     * @throws InvalidPipelineArgumentsException
     */
    public function setArgs(array $args): void
    {
        if (!($args[self::ARGUMENT_KEY_EXTRACTION] ?? null)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $args,
                message: sprintf(
                    'Extraction argument (%s) is required',
                    self::ARGUMENT_KEY_EXTRACTION,
                ),
            );
        }

        $this->extraction = $this->prepareExtractionArgument(
            extraction: $args[self::ARGUMENT_KEY_EXTRACTION],
            transformations: $args[self::ARGUMENT_KEY_TRANSFORMATIONS] ?? null,
            arguments: $args,
        );

        $tolerateExtractionException = $this->prepareTolerateExtractionExceptionArgument(
            tolerateExtractionException: $args[self::ARGUMENT_KEY_TOLERATE_EXTRACTION_EXCEPTION] ?? true,
            arguments: $args,
        );
        if ($tolerateExtractionException) {
            $this->tolerateExceptionFqcns[] = ExtractionExceptionInterface::class;
        }

        $tolerateTransformationException = $this->prepareTolerateTransformationExceptionArgument(
            tolerateTransformationException: $args[self::ARGUMENT_KEY_TOLERATE_TRANSFORMATION_EXCEPTION] ?? false,
            arguments: $args,
        );
        if ($tolerateTransformationException) {
            $this->tolerateExceptionFqcns[] = TransformationExceptionInterface::class;
        }
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws StageException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        try {
            $usablePayload = is_object($payload) && !($payload instanceof \Generator)
                ? clone $payload
                : $payload;

            $return = $this->extractor->extract(
                source: $usablePayload,
                accessor: $this->extraction?->accessor,
                transformations: $this->extraction?->transformations,
                context: $context,
            );
        } catch (\Throwable $exception) {
            if (!in_array($exception::class, $this->tolerateExceptionFqcns, true)) {
                throw new StageException(
                    pipeline: $this,
                    previous: $exception,
                );
            }

            $return = null;
        }

        return $return;
    }

    /**
     * @param mixed $extraction
     * @param mixed $transformations
     * @param mixed[]|null $arguments
     * @return Extraction
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareExtractionArgument(
        mixed $extraction,
        mixed $transformations,
        ?array $arguments,
    ): Extraction {
        switch (true) {
            case $extraction instanceof Extraction:
                break;

            case is_string($extraction) && str_starts_with($extraction, SyntaxParser::EXTRACTION_START_CHARACTER):
                $extractionArgument = $this->argumentConverter->execute($extraction);
                $accessor = $this->prepareExtractionArgument(
                    extraction: $extractionArgument->getValue(),
                    transformations: $transformations,
                    arguments: $arguments,
                );
                $extraction = new Extraction(
                    accessor: $accessor,
                );
                break;

            case is_string($extraction):
                $extraction = new Extraction(
                    accessor: $extraction,
                );
                break;

            default:
                throw new InvalidPipelineArgumentsException(
                    pipelineName: $this::class,
                    arguments: $arguments,
                    message: sprintf(
                        'Extraction argument (%s) must be instance of %s or accessor string; Received %s',
                        self::ARGUMENT_KEY_EXTRACTION,
                        Extraction::class,
                        get_debug_type($extraction),
                    ),
                );
        }

        switch (true) {
            case null === $transformations:
            case $transformations instanceof TransformationIterator:
                break;

            case is_array($transformations):
                $transformations = $this->transformationIteratorFactory->createFromSyntaxDeclaration(
                    syntaxDeclaration: implode(
                        separator: SyntaxParser::EXTRACTION_TRANSFORM_CHARACTER,
                        array: $transformations,
                    ),
                );
                break;

            case is_string($transformations):
                $transformations = $this->transformationIteratorFactory->createFromSyntaxDeclaration(
                    syntaxDeclaration: $transformations,
                );
                break;

            default:
                throw new InvalidPipelineArgumentsException(
                    pipelineName: Extract::class,
                    arguments: $arguments,
                    message: sprintf(
                        'Transformations argument (%s) must be instance of %s|%s[]|string; Received %s',
                        self::ARGUMENT_KEY_TRANSFORMATIONS,
                        TransformationIterator::class,
                        TransformationIterator::class,
                        get_debug_type($transformations),
                    ),
                );
        }

        if ($transformations?->count()) {
            $existingTransformations = $extraction->transformations ?? new TransformationIterator();
            $existingTransformations = $existingTransformations->merge($transformations);

            $extraction = new Extraction(
                accessor: $extraction->accessor,
                transformations: $existingTransformations,
            );
        }

        return $extraction;
    }

    /**
     * @param mixed $tolerateExtractionException
     * @param mixed[]|null $arguments
     * @return bool
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareTolerateExtractionExceptionArgument(
        mixed $tolerateExtractionException,
        ?array $arguments,
    ): bool {
        if (!is_bool($tolerateExtractionException)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Tolerate Extraction Exception argument (%s) must be boolean; Received %s',
                    self::ARGUMENT_KEY_TOLERATE_EXTRACTION_EXCEPTION,
                    get_debug_type($tolerateExtractionException),
                ),
            );
        }

        return $tolerateExtractionException;
    }

    /**
     * @param mixed $tolerateTransformationException
     * @param mixed[]|null $arguments
     * @return bool
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareTolerateTransformationExceptionArgument(
        mixed $tolerateTransformationException,
        ?array $arguments,
    ): bool {
        if (!is_bool($tolerateTransformationException)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Tolerate Transformation Exception argument (%s) must be boolean; Received %s',
                    self::ARGUMENT_KEY_TOLERATE_TRANSFORMATION_EXCEPTION,
                    get_debug_type($tolerateTransformationException),
                ),
            );
        }

        return $tolerateTransformationException;
    }
}
