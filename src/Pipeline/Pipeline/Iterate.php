<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Pipeline;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\HasErrorsExceptionInterface;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelinePayloadException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\PipelineInterface; // phpcs:ignore SlevomatCodingStandard.Namespaces.UnusedUses.UnusedUse
use Klevu\Pipelines\Traits\ConvertIterableToArrayTrait;
use Psr\Log\LoggerInterface;

class Iterate extends Pipeline
{
    use ConvertIterableToArrayTrait;

    final public const ARGUMENT_KEY_CONTINUE_ON_EXCEPTION = 'continueOnException';
    final public const ARGUMENT_KEY_MAX_ITERATIONS = 'maxIterations';

    /**
     * @var LoggerInterface|null
     */
    private readonly ?LoggerInterface $logger;
    /**
     * @var array<string, class-string[]|null>
     */
    private array $continueOnException = [];
    /**
     * @var int|null
     */
    private ?int $maxIterations = null;

    /**
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @param LoggerInterface|null $logger
     */
    public function __construct(
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
        ?LoggerInterface $logger = null,
    ) {
        parent::__construct($stages, $args, $identifier);

        $this->logger = $logger;
    }

    /**
     * @param mixed[] $args
     * @return void
     */
    public function setArgs(
        array $args, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        $this->continueOnException = $this->prepareContinueOnExceptionArgument(
            continueOnException: $args[self::ARGUMENT_KEY_CONTINUE_ON_EXCEPTION] ?? [],
            arguments: $args,
        );
        $this->maxIterations = $this->prepareMaxIterationsArgument(
            maxIterations: $args[self::ARGUMENT_KEY_MAX_ITERATIONS] ?? null,
            arguments: $args,
        );
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed[]
     * @throws ExtractionException
     * @throws TransformationException
     * @throws ValidationException
     * @throws InvalidInputDataException
     * @throws StageException
     * @throws \Throwable
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): array {
        $payload = $this->preparePayload($payload);

        $return = [];
        $currentIteration = 0;
        /**
         * @var string|int $index
         * @var mixed $payloadItem
         */
        foreach ($payload as $index => $payloadItem) {
            if (++$currentIteration > ($this->maxIterations ?? $currentIteration + 1)) {
                $this->logger?->debug(
                    message: 'Pipeline Iterate stage exited after max iterations reached',
                    context: [
                        'payloadItemIndex' => $index,
                        'currentIteration' => $currentIteration,
                        'maxIterations' => $this->maxIterations,
                        'stageIdentifier' => $this->getIdentifier(),
                    ],
                );

                break;
            }

            try {
                $return[] = parent::execute(
                    payload: $payloadItem,
                    context: $context,
                );
            } catch (StageException $exception) {
                $this->handleStageException(
                    index: $index,
                    exception: $exception,
                );
            }
        }

        return $return;
    }

    /**
     * @param mixed $payload
     * @return iterable<int|string, mixed>|\Generator
     * @throws InvalidPipelinePayloadException
     */
    private function preparePayload(
        mixed $payload,
    ): iterable|\Generator {
        switch (true) {
            case null === $payload:
                $payload = [];
                break;

            case is_array($payload):
            case $payload instanceof \Generator:
                break;

            case is_iterable($payload):
                try {
                    $payload = $this->convertIterableToArray($payload);
                } catch (\InvalidArgumentException $exception) {
                    throw new InvalidPipelinePayloadException(
                        pipelineName: $this::class,
                        message: sprintf(
                            'Invalid input data. Expected iterable|Generator, received %s',
                            get_debug_type($payload),
                        ),
                        previous: $exception,
                    );
                }
                break;

            default:
                throw new InvalidPipelinePayloadException(
                    pipelineName: $this::class,
                    message: sprintf(
                        'Invalid input data. Expected iterable|Generator, received %s',
                        get_debug_type($payload),
                    ),
                );
        }

        return $payload;
    }

    /**
     * @param int|string $index
     * @param StageException $exception
     * @return void
     * @throws \Throwable
     */
    private function handleStageException(
        int|string $index,
        StageException $exception,
    ): void {
        $sourceException = $exception->getPrevious();
        if (!$sourceException) {
            throw $exception;
        }

        $stage = $exception->getPipeline();
        $exceptionStageIdentifier = $stage->getIdentifier();

        $matchingStages = array_filter(
            array: $this->continueOnException,
            callback: static fn (string $stageIdentifier): bool => (
                ($stageIdentifier === $exceptionStageIdentifier)
                || str_starts_with(
                    haystack: $exceptionStageIdentifier,
                    needle: $stageIdentifier . '.',
                )
            ),
            mode: ARRAY_FILTER_USE_KEY,
        );
        if (!$matchingStages) {
            throw $exception;
        }

        $rethrow = true;
        foreach ($matchingStages as $expectedExceptions) {
            if (null === $expectedExceptions) {
                $rethrow = false;
                break;
            }

            foreach ($expectedExceptions as $expectedException) {
                if ($sourceException instanceof $expectedException) {
                    $rethrow = false;
                    break 2;
                }
            }
        }

        if ($rethrow) {
            throw $exception;
        }

        $this->logger?->debug(
            message: 'Pipeline Iterate stage skipped item',
            context: [
                'payloadItemIndex' => $index,
                'stageIdentifier' => $exceptionStageIdentifier,
                'exception' => $sourceException::class,
                'errors' => ($sourceException instanceof HasErrorsExceptionInterface)
                    ? $sourceException->getErrors()
                    : null,
            ],
        );
    }

    /**
     * @param mixed $continueOnException
     * @param mixed[]|null $arguments
     * @return array<string, class-string[]|null>
     */
    private function prepareContinueOnExceptionArgument(
        mixed $continueOnException,
        ?array $arguments,
    ): array {
        if (null !== $continueOnException && !is_array($continueOnException)) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: sprintf(
                    'Continue On Exception argument (%s) must be array|null; Received %s',
                    self::ARGUMENT_KEY_CONTINUE_ON_EXCEPTION,
                    get_debug_type($continueOnException),
                ),
            );
        }

        $errors = [];
        $return = [];
        foreach ($continueOnException ?? [] as $stageIdentifier => $exceptions) {
            $stageIdentifier = (string)$stageIdentifier;
            if (str_starts_with($stageIdentifier, '.')) {
                $stageIdentifier = $this->getIdentifier() . $stageIdentifier;
            }

            if (null === $exceptions) {
                $return[$stageIdentifier] = null;
                continue;
            }

            if (is_string($exceptions)) {
                $exceptions = [$exceptions];
            }
            if (!is_array($exceptions)) {
                $errors[] = sprintf(
                    'Continue On Exception argument (%s) stage [%s] values must be string[]|string|null; Received %s',
                    self::ARGUMENT_KEY_CONTINUE_ON_EXCEPTION,
                    $stageIdentifier,
                    get_debug_type($exceptions),
                );
                continue;
            }

            $return[$stageIdentifier] = [];
            foreach ($exceptions as $index => $exception) {
                if (!is_string($exception)) {
                    $errors[] = sprintf(
                        'Continue On Exception argument (%s) stage [%s] value (%s) must be string; Received %s',
                        self::ARGUMENT_KEY_CONTINUE_ON_EXCEPTION,
                        $stageIdentifier,
                        $index,
                        get_debug_type($exceptions),
                    );
                    continue;
                }

                /** @var class-string $exception */
                $return[$stageIdentifier][] = $exception;
            }
        }

        if ($errors) {
            throw new InvalidPipelineArgumentsException(
                pipelineName: $this::class,
                arguments: $arguments,
                message: implode(', ', $errors),
            );
        }

        return $return;
    }

    /**
     * @param mixed $maxIterations
     * @param mixed[]|null $arguments
     *
     * @return int|null
     * @throws InvalidPipelineArgumentsException
     */
    private function prepareMaxIterationsArgument(
        mixed $maxIterations,
        ?array $arguments,
    ): ?int {
        switch (true) {
            case is_numeric($maxIterations):
                $return = (int)$maxIterations;
                break;

            case null === $maxIterations:
                $return = null;
                break;

            default:
                throw new InvalidPipelineArgumentsException(
                    pipelineName: $this::class,
                    arguments: $arguments,
                    message: sprintf(
                        'Max Iterations argument (%s) must be int|null; Received %s',
                        self::ARGUMENT_KEY_MAX_ITERATIONS,
                        get_debug_type($maxIterations),
                    ),
                );
        }

        return $return;
    }
}
