<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineArgumentsException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineConfigurationException;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\PipelineFqcnProvider;
use Klevu\Pipelines\ObjectManager\PipelineFqcnProviderInterface;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class PipelineFactory implements PipelineFactoryInterface
{
    /**
     * @var PipelineFqcnProviderInterface
     */
    private readonly PipelineFqcnProviderInterface $pipelineFqcnProvider;

    /**
     * @param PipelineFqcnProviderInterface|null $pipelineFqcnProvider
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidClassException
     */
    public function __construct(
        ?PipelineFqcnProviderInterface $pipelineFqcnProvider = null,
    ) {
        $container = Container::getInstance();

        if (null === $pipelineFqcnProvider) {
            $pipelineFqcnProvider = $container->get(PipelineFqcnProvider::class);
            if (!($pipelineFqcnProvider instanceof PipelineFqcnProviderInterface)) {
                throw new InvalidClassException(
                    identifier: PipelineFqcnProvider::class,
                    instance: $pipelineFqcnProvider,
                );
            }
        }
        $this->pipelineFqcnProvider = $pipelineFqcnProvider;
    }

    /**
     * @param string $pipelineAlias
     * @param mixed[]|null $args
     * @param PipelineInterface[] $stages
     * @param mixed[] $constructorArgs
     * @return PipelineInterface
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     * @throws InvalidPipelineArgumentsException
     * @throws InvalidPipelineConfigurationException
     */
    public function create(
        string $pipelineAlias,
        ?array $args = null,
        array $stages = [],
        array $constructorArgs = [],
    ): PipelineInterface {
        $container = Container::getInstance();

        $pipelineFqcn = $this->pipelineFqcnProvider->getFqcn($pipelineAlias);
        if (null === $pipelineFqcn) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: $pipelineAlias,
                message: sprintf(
                    'Cannot find pipeline for alias %s',
                    $pipelineAlias,
                ),
            );
        }

        $pipeline = $container->create($pipelineFqcn, $constructorArgs);

        if (!($pipeline instanceof PipelineInterface)) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: $pipelineFqcn,
                message: sprintf(
                    'Pipeline %s of type %s does not implement %s',
                    $pipelineFqcn,
                    $pipeline::class,
                    PipelineInterface::class,
                ),
            );
        }

        if ($args) {
            $pipeline->setArgs($args);
        }

        foreach ($stages as $identifier => $stage) {
            $pipeline->addStage(
                stage: $stage,
                identifier: is_string($identifier) ? $identifier : null,
            );
        }

        return $pipeline;
    }
}
