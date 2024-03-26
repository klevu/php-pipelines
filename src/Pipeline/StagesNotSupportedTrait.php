<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\PipelineException;

trait StagesNotSupportedTrait
{
    /**
     * @param PipelineInterface $stage
     * @param string|null $identifier
     * @return void
     * @throws PipelineException
     */
    public function addStage(
        PipelineInterface $stage, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
        ?string $identifier = null, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        throw new PipelineException(
            pipelineName: $this::class,
            message: sprintf(
                'Stages are not supported for pipelines of type %s',
                $this::class,
            ),
        );
    }
}
