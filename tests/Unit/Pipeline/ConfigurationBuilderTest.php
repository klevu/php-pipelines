<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Pipeline;

use Klevu\Pipelines\Pipeline\ConfigurationBuilder;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ConfigurationBuilder::class)]
class ConfigurationBuilderTest extends TestCase
{
    #[Test]
    public function testBuild(): void
    {
        $pipelineDefinition = [
            'stages' => [
                'firstStage' => [
                    'pipeline' => 'Stage\Extract',
                    'args' => [
                        'extraction' => 'foo',
                    ],
                ],
                'secondStage' => [
                    'stages' => [],
                    'args' => [],
                ],
            ],
        ];

        $expectedResult = [
            'pipeline' => 'Pipeline',
            'args' => [],
            'stages' => [
                'firstStage' => [
                    'pipeline' => 'Stage\Extract',
                    'args' => [
                        'extraction' => 'foo',
                    ],
                    'stages' => [],
                ],
                'secondStage' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
            ],
        ];

        $configurationBuilder = new ConfigurationBuilder();
        $actualResult = $configurationBuilder->build(
            pipelineDefinition: $pipelineDefinition,
            pipelineOverrides: [],
        );

        $this->assertSame(
            $expectedResult,
            $actualResult,
        );
    }

    #[Test]
    public function testBuild_WithOverrides(): void
    {
        $pipelineDefinition = [
            'stages' => [
                'firstStage' => [
                    'pipeline' => 'Stage\Extract',
                    'args' => [
                        'extraction' => 'foo',
                    ],
                ],
                'secondStage' => [
                    'stages' => [],
                    'args' => [],
                ],
            ],
        ];
        $pipelineOverrides = [
            [
                'pipeline' => 'ParentPipeline',
                'addStages' => [
                    'log' => [
                        'before' => null,
                        'pipeline' => 'Stage\Log',
                    ],
                ],
                'removeStages' => [
                    'firstStage',
                ],
                'stages' => [
                    'secondStage' => [
                        'pipeline' => 'TestPipeline',
                    ],
                ],
            ],
        ];

        $expectedResult = [
            'pipeline' => 'ParentPipeline',
            'args' => [],
            'stages' => [
                'secondStage' => [
                    'pipeline' => 'TestPipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'log' => [
                    'pipeline' => 'Stage\Log',
                    'args' => [],
                    'stages' => [],
                ],
            ],
        ];

        $configurationBuilder = new ConfigurationBuilder();
        $actualResult = $configurationBuilder->build(
            pipelineDefinition: $pipelineDefinition,
            pipelineOverrides: $pipelineOverrides,
        );

        $this->assertSame(
            $expectedResult,
            $actualResult,
        );
    }

    #[Test]
    public function testBuildFromFiles_WithOverrides(): void
    {
        $primaryConfigurationFile = __DIR__ . '/../../Fixture/etc/pipeline-schema/pipeline.yml';
        $overridesFiles = [
            __DIR__ . '/../../Fixture/etc/pipeline-schema/pipeline-overrides.yml',
        ];

        $configurationBuilder = new ConfigurationBuilder();
        $actualResult = $configurationBuilder->buildFromFiles(
            pipelineDefinitionFile: $primaryConfigurationFile,
            pipelineOverridesFiles: $overridesFiles,
        );

        $expectedResult = [
            'pipeline' => 'ParentPipeline',
            'args' => [],
            'stages' => [
                'log' => [
                    'pipeline' => 'Stage\Log',
                    'args' => [],
                    'stages' => [],
                ],
                'secondStage' => [
                    'pipeline' => 'TestPipeline',
                    'args' => [],
                    'stages' => [],
                ],
            ],
        ];

        $this->assertEquals(
            $expectedResult,
            $actualResult,
        );
    }

    #[Test]
    public function testBuild_AddStages(): void
    {
        $this->markTestSkipped('Todo: Resolve issues with complex interrelated reordering');
    }

    /**
     * Added work in progress for testBuild_AddStages as separate private method
     *  to prevent phpstan complaining about unreachable code after markTestSkipped
     * Public so phpstan doesn't complain about unused method
     *
     * @todo
     */
    public function wip_testBuild_AddStages(): void
    {
        $pipelineDefinition = [
            'stages' => [
                'stage-1' => [
                    'stages' => [
                        'stage-1-1' => [],
                        'stage-1-2' => [],
                    ],
                ],
                'stage-2' => [],
                [
                    'pipeline' => 'IntegerIndexed',
                ],
            ],
        ];
        $pipelineOverrides = [
            // Append, Before
            [
                'stages' => [
                    'stage-1' => [
                        'addStages' => [
                            'new-1-stage-1-1' => [
                                'pipeline' => 'Test',
                            ],
                            'new-1-stage-1-2' => [
                                'before' => '-',
                            ],
                            'new-1-stage-1-3' => [
                                'before' => 'new-1-stage-1-2',
                            ],
                        ],
                    ],
                ],
                'addStages' => [
                    'new-1-stage-1' => [],
                    'new-1-stage-2' => [
                        'before' => 0,
                    ],
                ],
            ],
            // Append, Before
            [
                'addStages' => [
                    'new-2-stage-1' => [],
                ],
                'stages' => [
                    'stage-2' => [
                        'addStages' => [
                            '' => [
                                'pipeline' => 'EmptyStringIndexed_1',
                            ],
                        ],
                    ],
                    'stage-1' => [
                        'addStages' => [
                            'new-2-stage-1-1' => [
                                'before' => '-',
                            ],
                            [
                                'pipeline' => 'IntegerIndexed_2',
                            ],
                        ],
                    ],
                ],
            ],
            // Before
            [
                'addStages' => [
                    'new-3-stage-1' => [
                        'before' => 'stage-1-1',
                    ],
                    'new-3-stage-2' => [
                        'before' => 'new-2-stage-1',
                    ],
                ],
            ],
            // Append
            [
                'stages' => [
                    'stage-2' => [
                        'addStages' => [
                            '' => [
                                'pipeline' => 'EmptyStringIndexed_2',
                            ],
                        ],
                    ],
                ],
            ],
            // After (not yet existent)
            [
                'addStages' => [
                    'new-5-stage-1' => [
                        'after' => 'new-6-stage-1',
                    ],
                ],
            ],
            // After
            [
                'addStages' => [
                    'new-6-stage-1' => [
                        'after' => 'new-1-stage-2',
                    ],
                ],
            ],
        ];

        /**
         * Expected generated order
         *  stage-1
         *      new-2-stage-1-1
         *      new-1-stage-1-3
         *      new-1-stage-1-2
         *      stage-1-1
         *      stage-1-2
         *      new-1-stage-1-1
         *      0
         *  stage-2
         *      0
         *      1
         *  new-1-stage-2
         *  new-6-stage-1
         *  new-5-stage-1
         *  0
         *  new-1-stage-1
         *  new-3-stage-2
         *  new-2-stage-1
         *  new-3-stage-1
         */

        $expectedResult = [
            'pipeline' => 'Pipeline',
            'args' => [],
            'stages' => [
                'stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [
                        'new-2-stage-1-1' => [
                            'pipeline' => 'Pipeline',
                            'args' => [],
                            'stages' => [],
                        ],
                        'new-1-stage-1-3' => [
                            'pipeline' => 'Pipeline',
                            'args' => [],
                            'stages' => [],
                        ],
                        'new-1-stage-1-2' => [
                            'pipeline' => 'Pipeline',
                            'args' => [],
                            'stages' => [],
                        ],
                        'stage-1-1' => [
                            'pipeline' => 'Pipeline',
                            'args' => [],
                            'stages' => [],
                        ],
                        'stage-1-2' => [
                            'pipeline' => 'Pipeline',
                            'args' => [],
                            'stages' => [],
                        ],
                        'new-1-stage-1-1' => [
                            'pipeline' => 'Test',
                            'args' => [],
                            'stages' => [],
                        ],
                        0 => [
                            'pipeline' => 'IntegerIndexed_2',
                            'args' => [],
                            'stages' => [],
                        ],
                    ],
                ],
                'stage-2' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [
                        0 => [
                            'pipeline' => 'EmptyStringIndexed_1',
                            'args' => [],
                            'stages' => [],
                        ],
                        1 => [
                            'pipeline' => 'EmptyStringIndexed_2',
                            'args' => [],
                            'stages' => [],
                        ],
                    ],
                ],
                'new-1-stage-2' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'new-6-stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'new-5-stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                0 => [
                    'pipeline' => 'IntegerIndexed',
                    'args' => [],
                    'stages' => [],
                ],
                'new-1-stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'new-3-stage-2' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'new-2-stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
                'new-3-stage-1' => [
                    'pipeline' => 'Pipeline',
                    'args' => [],
                    'stages' => [],
                ],
            ],
        ];

        $configurationBuilder = new ConfigurationBuilder();
        $actualResult = $configurationBuilder->build(
            pipelineDefinition: $pipelineDefinition,
            pipelineOverrides: $pipelineOverrides,
        );

        $this->assertSame(
            $expectedResult,
            $actualResult,
        );
    }
}
