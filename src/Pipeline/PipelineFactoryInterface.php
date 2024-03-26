<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineConfigurationException;
use Klevu\Pipelines\Exception\PipelineException;

interface PipelineFactoryInterface
{
    /**
     * @param string $pipelineAlias
     * @param mixed[]|null $args
     * @param PipelineInterface[] $stages
     * @param mixed[] $constructorArgs
     * @return PipelineInterface
     * @throws PipelineException
     * @throws InvalidPipelineArgumentsException
     * @throws InvalidPipelineConfigurationException
     */
    public function create(
        string $pipelineAlias,
        ?array $args = null,
        array $stages = [],
        array $constructorArgs = [],
    ): PipelineInterface;
}
