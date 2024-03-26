<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline\Pipeline;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;
use Klevu\Pipelines\Exception\ValidationExceptionInterface;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Pipeline\StagesTrait;

class Fallback implements PipelineInterface
{
    use StagesTrait;

    /**
     * @var string
     */
    private readonly string $identifier;

    /**
     * @param PipelineInterface[] $stages
     * @param mixed[]|null $args
     * @param string $identifier
     * @throws \RuntimeException
     */
    public function __construct(
        array $stages = [],
        ?array $args = null,
        string $identifier = '',
    ) {
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
    public function setArgs(
        array $args, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        // No args supported for this pipeline
    }

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ExtractionException
     * @throws TransformationException
     * @throws ValidationException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        $usablePayload = is_object($payload)
            ? clone $payload
            : $payload;

        $return = null;
        foreach ($this->stages as $stage) {
            try {
                $return = $stage->execute(
                    payload: $usablePayload,
                    context: $context,
                );
                break;
            } catch (StageException $exception) {
                if (!($exception->getPrevious() instanceof ValidationExceptionInterface)) {
                    throw $exception;
                }
            }
        }

        return $return;
    }
}
