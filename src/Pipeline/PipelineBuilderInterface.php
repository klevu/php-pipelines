<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineConfigurationException;
use Klevu\Pipelines\Exception\PipelineException;

interface PipelineBuilderInterface
{
    /**
     * @param mixed[] $configuration
     * @return PipelineInterface
     * @throws PipelineException
     * @throws InvalidPipelineConfigurationException
     */
    public function build(array $configuration): PipelineInterface;

    /**
     * @param string $configurationFilepath
     * @param string[] $overridesFilepaths
     * @return PipelineInterface
     * @throws PipelineException
     * @throws InvalidPipelineConfigurationException
     */
    public function buildFromFiles(
        string $configurationFilepath,
        array $overridesFilepaths = [],
    ): PipelineInterface;
}
