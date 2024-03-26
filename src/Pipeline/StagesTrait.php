<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

trait StagesTrait
{
    /**
     * @var PipelineInterface[]
     */
    private array $stages = [];

    /**
     * @param PipelineInterface $stage
     * @param string|null $identifier
     * @return void
     * @throws \RuntimeException
     */
    public function addStage(PipelineInterface $stage, ?string $identifier = null): void
    {
        if (spl_object_hash($this) === spl_object_hash($stage)) {
            throw new \RuntimeException('Cannot add pipeline as stage of itself');
        }

        if (null !== $identifier && '' !== $identifier) {
            $this->stages[$identifier] = $stage;
        } else {
            $this->stages[] = $stage;
        }
    }
}
