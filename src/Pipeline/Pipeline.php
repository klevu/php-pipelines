<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\ExtractionException;
use Klevu\Pipelines\Exception\Pipeline\StageException;
use Klevu\Pipelines\Exception\TransformationException;
use Klevu\Pipelines\Exception\ValidationException;

class Pipeline implements PipelineInterface
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
     * @throws StageException
     */
    public function execute(
        mixed $payload,
        ?\ArrayAccess $context = null,
    ): mixed {
        $return = is_object($payload) && !($payload instanceof \Generator)
            ? clone $payload
            : $payload;
        foreach ($this->stages as $stage) {
            $return = $stage->execute(
                payload: $return,
                context: $context,
            );
        }

        return $return;
    }
}
