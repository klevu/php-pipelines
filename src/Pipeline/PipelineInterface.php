<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\ExtractionExceptionInterface;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationExceptionInterface;
use Klevu\Pipelines\Exception\ValidationExceptionInterface;

interface PipelineInterface
{
    /**
     * @return string
     */
    public function getIdentifier(): string;

    /**
     * @param PipelineInterface $stage
     * @param string|null $identifier
     * @return void
     */
    public function addStage(PipelineInterface $stage, ?string $identifier = null): void;

    /**
     * @param mixed[] $args
     * @return void
     * @throws InvalidPipelineArgumentsException
     */
    public function setArgs(array $args): void;

    /**
     * @param mixed $payload
     * @param \ArrayAccess<string|int, mixed>|null $context
     * @return mixed
     * @throws ExtractionExceptionInterface
     * @throws TransformationExceptionInterface
     * @throws ValidationExceptionInterface
     * @throws StageException
     */
    public function execute(mixed $payload, ?\ArrayAccess $context = null): mixed;
}
