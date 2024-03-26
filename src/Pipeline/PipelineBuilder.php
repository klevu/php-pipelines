<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\ObjectManager\InvalidClassException;
use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineConfigurationException;
use Klevu\Pipelines\Exception\PipelineException;
use Klevu\Pipelines\ObjectManager\Container;
use Klevu\Pipelines\ObjectManager\ObjectManagerInterface;
use Klevu\Pipelines\ObjectManager\TransformerManager;
use Klevu\Pipelines\ObjectManager\ValidatorManager;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;
use Psr\Log\LoggerInterface;

class PipelineBuilder implements PipelineBuilderInterface
{
    /**
     * @var ConfigurationBuilder
     */
    private readonly ConfigurationBuilder $configurationBuilder;
    /**
     * @var PipelineFactoryInterface
     */
    private readonly PipelineFactoryInterface $pipelineFactory;
    /**
     * @var ObjectManagerInterface
     */
    private readonly ObjectManagerInterface $transformerManager;
    /**
     * @var ObjectManagerInterface
     */
    private readonly ObjectManagerInterface $validatorManager;
    /**
     * @var LoggerInterface|null
     */
    private readonly ?LoggerInterface $logger;
    /**
     * @var string
     */
    private readonly string $defaultPipeline;

    /**
     * @param ConfigurationBuilder|null $configurationBuilder
     * @param ObjectManagerInterface|null $transformerManager
     * @param ObjectManagerInterface|null $validatorManager
     * @param PipelineFactoryInterface|null $pipelineFactory
     * @param LoggerInterface|null $logger
     * @param string|null $defaultPipeline
     *  @throws ContainerExceptionInterface
     *  @throws NotFoundExceptionInterface
     *  @throws InvalidClassException
     */
    public function __construct(
        ?ConfigurationBuilder $configurationBuilder = null,
        ?ObjectManagerInterface $transformerManager = null,
        ?ObjectManagerInterface $validatorManager = null,
        ?PipelineFactoryInterface $pipelineFactory = null,
        ?LoggerInterface $logger = null,
        ?string $defaultPipeline = null,
    ) {
        $container = Container::getInstance();
        if (!$container->has($this::class) && $container instanceof ObjectManagerInterface) {
            $container->addSharedInstance(
                identifier: $this::class,
                instance: $this,
            );
        }

        if (null === $configurationBuilder) {
            $configurationBuilder = $container->get(ConfigurationBuilder::class);
            if (!($configurationBuilder instanceof ConfigurationBuilder)) {
                throw new InvalidClassException(
                    identifier: ConfigurationBuilder::class,
                    instance: $configurationBuilder,
                );
            }
        }
        $this->configurationBuilder = $configurationBuilder;

        if (null === $pipelineFactory) {
            $pipelineFactory = $container->get(PipelineFactory::class);
            if (!($pipelineFactory instanceof PipelineFactoryInterface)) {
                throw new InvalidClassException(
                    identifier: PipelineFactory::class,
                    instance: $pipelineFactory,
                );
            }
        }
        $this->pipelineFactory = $pipelineFactory;

        $this->transformerManager = $transformerManager ?: new TransformerManager();
        $this->validatorManager = $validatorManager ?: new ValidatorManager();
        $this->logger = $logger;

        if (null === $defaultPipeline) {
            $defaultPipeline = 'Pipeline';
        }
        $this->defaultPipeline = $defaultPipeline;
    }

    /**
     * @param mixed[] $configuration
     * @param string $identifier
     * @return PipelineInterface
     * @throws PipelineException
     * @throws InvalidPipelineConfigurationException
     */
    public function build(
        array $configuration,
        string $identifier = '',
    ): PipelineInterface {
        $pipelineFqcn = $configuration[ConfigurationElements::PIPELINE->value] ?? $this->defaultPipeline;
        if (!is_string($pipelineFqcn)) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: '',
                message: sprintf(
                    'Invalid configuration value for pipeline element [%s]. Expected string, received %s',
                    ConfigurationElements::PIPELINE->value,
                    get_debug_type($pipelineFqcn),
                ),
            );
        }

        $args = $configuration[ConfigurationElements::ARGS->value] ?? null;
        if (null !== $args && !is_array($args)) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: $pipelineFqcn,
                message: sprintf(
                    'Invalid type for args element [%s]. Expecting array|null; Received %s',
                    ConfigurationElements::ARGS->value,
                    get_debug_type($args),
                ),
            );
        }
        $args = array_merge(
            [
                'transformerManager' => $this->transformerManager,
                'validatorManager' => $this->validatorManager,
            ],
            $configuration[ConfigurationElements::ARGS->value] ?? [],
        );

        $stages = $configuration[ConfigurationElements::STAGES->value] ?? [];
        if (!is_array($stages)) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: $pipelineFqcn,
                message: sprintf(
                    'Invalid type for stages element [%s]. Expecting array|null; Received %s',
                    ConfigurationElements::STAGES->value,
                    get_debug_type($stages),
                ),
            );
        }

        try {
            array_walk(
                $stages,
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                function (array &$stage, string|int $stageIdentifier) use ($identifier): void {
                    $stage = $this->build(
                        configuration: $stage,
                        identifier: (($identifier) ? $identifier . '.' : '') . $stageIdentifier,
                    );
                },
            );
        } catch (\TypeError $exception) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: $pipelineFqcn,
                message: sprintf(
                    'Error building stages: %s %s',
                    $exception->getMessage(),
                    json_encode($stages),
                ),
                previous: $exception,
            );
        }

        $constructorArgs = [
            'identifier' => $identifier,
            'logger' => $this->logger,
            'pipelineFactory' => $this->pipelineFactory,
            'transformerManager' => $this->transformerManager,
            'validatorManager' => $this->validatorManager,
        ];

        return $this->pipelineFactory->create(
            pipelineAlias: $pipelineFqcn,
            args: $args,
            stages: $stages,
            constructorArgs: $this->getProcessedConstructorArgs($constructorArgs),
        );
    }

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
    ): PipelineInterface {
        $configuration = $this->configurationBuilder->buildFromFiles(
            pipelineDefinitionFile: $configurationFilepath,
            pipelineOverridesFiles: $overridesFilepaths,
        );

        return $this->build($configuration);
    }

    /**
     * Entry point for overriding in platform implementations which may have
     *  requirements in their own object managers
     *
     * @param mixed[] $constructorArgs
     * @return mixed[]
     */
    protected function getProcessedConstructorArgs(
        array $constructorArgs,
    ): array {
        return $constructorArgs;
    }
}
