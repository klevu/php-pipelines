<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Pipeline;

use Klevu\Pipelines\Exception\Pipeline\InvalidPipelineConfigurationException;
use Symfony\Component\Yaml\Exception\ParseException;
use Symfony\Component\Yaml\Yaml;

class ConfigurationBuilder
{
    public const INSTRUCTION_ADD_STAGES = 'addStages';
    public const INSTRUCTION_REMOVE_STAGES = 'removeStages';
    public const STAGE_ARGUMENT_BEFORE = 'before';
    public const STAGE_ARGUMENT_AFTER = 'after';

    /**
     * @var string
     */
    private readonly string $defaultPipeline;

    /**
     * @param string|null $defaultPipeline
     */
    public function __construct(
        ?string $defaultPipeline = null,
    ) {
        if (null === $defaultPipeline) {
            $defaultPipeline = 'Pipeline';
        }
        $this->defaultPipeline = $defaultPipeline;
    }

    /**
     * @param mixed[] $pipelineDefinition
     * @param mixed[][] $pipelineOverrides
     * @return mixed[]
     */
    public function build(
        array $pipelineDefinition,
        array $pipelineOverrides = [],
    ): array {
        $configuration = $this->processStage($pipelineDefinition);

        foreach ($pipelineOverrides as $pipelineOverride) {
            $configuration = $this->applyStageOverrides(
                $configuration,
                $pipelineOverride,
            );
        }

        $configuration = $this->postProcessStage($configuration);

        return $configuration;
    }

    /**
     * @param string $pipelineDefinitionFile
     * @param string[] $pipelineOverridesFiles
     * @return mixed[]
     * @throws InvalidPipelineConfigurationException
     */
    public function buildFromFiles(
        string $pipelineDefinitionFile,
        array $pipelineOverridesFiles = [],
    ): array {
        try {
            $pipelineDefinition = (array)Yaml::parseFile($pipelineDefinitionFile);
            $pipelineOverrides = array_map(
                static fn (string $pipelineOverridesFile): array => (array)Yaml::parseFile(
                    $pipelineOverridesFile,
                ),
                $pipelineOverridesFiles,
            );
        } catch (ParseException $exception) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: null,
                message: $exception->getMessage(),
                previous: $exception,
            );
        }

        $pipelineDefinition = $this->replaceImportDirectives(
            pipelineDefinition: $pipelineDefinition,
            baseDirectory: dirname($pipelineDefinitionFile),
        );
        $pipelineOverrides = array_map(
            fn (array $pipelineOverride): array => $this->replaceImportDirectives(
                pipelineDefinition: $pipelineOverride,
                baseDirectory: dirname($pipelineDefinitionFile),
            ),
            $pipelineOverrides,
        );

        return $this->build(
            pipelineDefinition: $pipelineDefinition,
            pipelineOverrides: $pipelineOverrides,
        );
    }

    /**
     * @param string $importFilepath
     * @param string $baseDirectory
     *
     * @return string
     * @throws InvalidPipelineConfigurationException
     */
    public function getImportFilePath(string $importFilepath, string $baseDirectory): string
    {
        if (!str_starts_with($importFilepath, DIRECTORY_SEPARATOR)) {
            $importFilepath = rtrim($baseDirectory, DIRECTORY_SEPARATOR)
                . DIRECTORY_SEPARATOR
                . $importFilepath;
        }
        if (!is_readable($importFilepath)) {
            throw new InvalidPipelineConfigurationException(
                pipelineName: ConfigurationElements::IMPORT->value,
                message: sprintf(
                    'Cannot read import filepath: %s',
                    $importFilepath,
                ),
            );
        }

        return $importFilepath;
    }

    /**
     * @param mixed[] $pipelineDefinition
     * @param string $baseDirectory
     * @return mixed[]
     */
    private function replaceImportDirectives(
        array $pipelineDefinition,
        string $baseDirectory,
    ): array {
        if (is_array($pipelineDefinition[ConfigurationElements::STAGES->value] ?? null)) {
            array_walk(
                array: $pipelineDefinition[ConfigurationElements::STAGES->value],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                callback: function (mixed &$stageConfiguration) use ($baseDirectory): void {
                    if (!is_array($stageConfiguration)) {
                        throw new \InvalidArgumentException(
                            sprintf(
                                'stageConfiguration must be array; received %s',
                                is_scalar($stageConfiguration)
                                    ? json_encode($stageConfiguration)
                                    : get_debug_type($stageConfiguration),
                            ),
                        );
                    }

                    $importFilepath = $stageConfiguration[ConfigurationElements::IMPORT->value] ?? null;
                    if ($importFilepath) {
                        $stageConfiguration = $this->buildFromFiles(
                            pipelineDefinitionFile: $this->getImportFilePath($importFilepath, $baseDirectory),
                        );
                        return;
                    }

                    $stages = $stageConfiguration[ConfigurationElements::STAGES->value] ?? null;
                    if (is_array($stages)) {
                        $stageConfiguration[ConfigurationElements::STAGES->value] = array_map(
                            fn (array $childStageConfiguration): array => $this->replaceImportDirectives(
                                pipelineDefinition: $childStageConfiguration,
                                baseDirectory: $baseDirectory,
                            ),
                            $stages,
                        );
                    }
                    $addStages = $stageConfiguration[ConfigurationElements::ADD_STAGES->value] ?? null;
                    if (is_array($addStages)) {
                        $stageConfiguration[ConfigurationElements::ADD_STAGES->value] = array_map(
                            fn (array $addStageConfiguration): array => $this->replaceImportDirectives(
                                pipelineDefinition: $addStageConfiguration,
                                baseDirectory: $baseDirectory,
                            ),
                            $addStages,
                        );
                    }
                },
            );
        }
        $addStages = $pipelineDefinition[ConfigurationElements::ADD_STAGES->value] ?? null;
        if (is_array($addStages)) {
            $pipelineDefinition[ConfigurationElements::ADD_STAGES->value] = array_map(
                fn (array $addStageConfiguration): array => $this->replaceImportDirectives(
                    pipelineDefinition: $addStageConfiguration,
                    baseDirectory: $baseDirectory,
                ),
                $addStages,
            );
        }

        $importFilepath = $pipelineDefinition[ConfigurationElements::IMPORT->value] ?? null;
        if ($importFilepath) {
            $pipelineDefinition = $this->buildFromFiles(
                pipelineDefinitionFile: $this->getImportFilePath($importFilepath, $baseDirectory),
            );
        }

        return $pipelineDefinition;
    }

    /**
     * @param mixed[] $stageConfiguration
     * @return mixed[]
     */
    private function processStage(
        array $stageConfiguration,
    ): array {
        if (!($stageConfiguration[ConfigurationElements::PIPELINE->value] ?? null)) {
            $stageConfiguration[ConfigurationElements::PIPELINE->value] = $this->defaultPipeline;
        }

        if (!isset($stageConfiguration[ConfigurationElements::ARGS->value])) {
            $stageConfiguration[ConfigurationElements::ARGS->value] = [];
        }

        if (isset($stageConfiguration[ConfigurationElements::STAGES->value])) {
            $stageConfiguration[ConfigurationElements::STAGES->value] = array_map(
                [$this, 'processStage'],
                $stageConfiguration[ConfigurationElements::STAGES->value],
            );
        } else {
            $stageConfiguration[ConfigurationElements::STAGES->value] = [];
        }

        $stageConfiguration[static::STAGE_ARGUMENT_BEFORE] ??= null;
        $stageConfiguration[static::STAGE_ARGUMENT_AFTER] ??= null;

        $keyOrder = [
            ConfigurationElements::PIPELINE->value => 0,
            ConfigurationElements::ARGS->value => 1,
            ConfigurationElements::STAGES->value => 2,
        ];
        uksort(
            $stageConfiguration,
            static fn (string $keyA, string $keyB): int => (
                ($keyOrder[$keyA] ?? 999) <=> ($keyOrder[$keyB] ?? 999)
            ),
        );

        return $stageConfiguration;
    }

    /**
     * @param mixed[] $stageConfiguration
     * @return mixed[]
     */
    private function postProcessStage(
        array $stageConfiguration,
    ): array {
        if (!is_array($stageConfiguration[ConfigurationElements::STAGES->value] ?? null)) {
            $stageConfiguration[ConfigurationElements::STAGES->value] = [];
        }

        $stageConfiguration[ConfigurationElements::STAGES->value] = $this->sortStages(
            stages: $stageConfiguration[ConfigurationElements::STAGES->value],
        );
        $keysToRemove = array_diff(
            array_keys($stageConfiguration),
            array_map(
                static fn (ConfigurationElements $element): string => $element->value,
                ConfigurationElements::cases(),
            ),
        );
        foreach ($keysToRemove as $keyToRemove) {
            unset($stageConfiguration[$keyToRemove]);
        }

        $stageConfiguration[ConfigurationElements::STAGES->value] = array_map(
            callback: [$this, 'postProcessStage'],
            array: $stageConfiguration[ConfigurationElements::STAGES->value],
        );

        return $stageConfiguration;
    }

    /**
     * @param mixed[][] $stages
     * @return mixed[][]
     *
     * @todo Resolve issues with complex interrelated reordering
     */
    private function sortStages(
        array $stages,
    ): array {
        $position = 0;
        $return = array_map(
            // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedInheritingVariableByReference
            static function (array $stageConfiguration) use (&$position): array {
                return array_merge(
                    $stageConfiguration,
                    ['position' => ($position += 100)],
                );
            },
            $stages,
        );

        // Run twice to catch relative ordering on "before" / "after" '-' items
        for ($iteration = 1; $iteration <= 2; $iteration++) {
            foreach ($stages as $stageIdentifier => $stageConfiguration) {
                $before = $stageConfiguration[static::STAGE_ARGUMENT_BEFORE] ?? null;
                if ($before) {
                    $return[$stageIdentifier]['position'] = match (true) {
                        ('-' === $before) => min(array_column($return, 'position')) - 100,
                        isset($return[$before]) => ($return[$before]['position'] ?? 0) - 10,
                        default => ($return[$stageIdentifier]['position'] ?? 0),
                    };
                }

                $after = $stageConfiguration[static::STAGE_ARGUMENT_AFTER] ?? null;
                if ($after) {
                    $return[$stageIdentifier]['position'] = match (true) {
                        ('-' === $after) => max(array_column($return, 'position')) + 100,
                        isset($return[$after]) => ($return[$after]['position'] ?? 0) + 10,
                        default => ($return[$stageIdentifier]['position'] ?? 0),
                    };
                }
            }
        }

        uasort(
            $return,
            static fn (array $a, array $b): int => ($a['position'] <=> $b['position']),
        );

        return $return;
    }

    /**
     * @param mixed[] $stageConfiguration
     * @param mixed[] $configurationOverride
     * @return mixed[]
     */
    private function applyStageOverrides(
        array $stageConfiguration,
        array $configurationOverride,
    ): array {
        // Add stage(s)
        $addStagesOverrides = $configurationOverride[static::INSTRUCTION_ADD_STAGES] ?? null;
        if ($addStagesOverrides && is_array($addStagesOverrides)) {
            $stages = $stageConfiguration[ConfigurationElements::STAGES->value] ?? [];
            if (!is_array($stages)) {
                $stages = [];
            }

            foreach ($addStagesOverrides as $newStageIdentifier => $newStage) {
                if (is_int($newStageIdentifier) || '' === $newStageIdentifier) {
                    $stages[] = $this->processStage($newStage);
                } else {
                    $stages[$newStageIdentifier] = $this->processStage($newStage);
                }
            }

            $stageConfiguration[ConfigurationElements::STAGES->value] = $stages;
        }

        // Remove stage(s)
        $removeStagesOverrides = $configurationOverride[static::INSTRUCTION_REMOVE_STAGES] ?? null;
        if (is_array($removeStagesOverrides)) {
            foreach ($removeStagesOverrides as $removeStageIdentifier) {
                unset($stageConfiguration[ConfigurationElements::STAGES->value][$removeStageIdentifier]);
            }
        }

        // Update existing stage(s)
        $stagesOverrides = $configurationOverride[ConfigurationElements::STAGES->value] ?? null;
        if (is_array($stagesOverrides)) {
            foreach ($stagesOverrides as $stageIdentifier => $stageOverride) {
                if (!isset($stageConfiguration[ConfigurationElements::STAGES->value][$stageIdentifier])) {
                    continue;
                }

                $stageConfiguration[ConfigurationElements::STAGES->value][$stageIdentifier] = $this->applyStageOverrides( // phpcs:ignore Generic.Files.LineLength.TooLong
                    stageConfiguration: $stageConfiguration[ConfigurationElements::STAGES->value][$stageIdentifier],
                    configurationOverride: $stageOverride,
                );
            }
        }

        // Change pipeline type
        $pipelineOverride = $configurationOverride[ConfigurationElements::PIPELINE->value] ?? null;
        if ($pipelineOverride) {
            $stageConfiguration[ConfigurationElements::PIPELINE->value] = $pipelineOverride;
        }

        // Modify arguments
        $argsOverrides = $configurationOverride[ConfigurationElements::ARGS->value] ?? null;
        if (is_array($argsOverrides)) {
            $stageConfiguration[ConfigurationElements::ARGS->value] = array_merge(
                $stageConfiguration[ConfigurationElements::ARGS->value],
                $argsOverrides,
            );
        }

        return $stageConfiguration;
    }
}
