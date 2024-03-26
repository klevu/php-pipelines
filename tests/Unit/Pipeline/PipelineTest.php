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

use Klevu\Pipelines\Pipeline\Pipeline;
use Klevu\Pipelines\Pipeline\PipelineInterface;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Pipeline::class)]
class PipelineTest extends TestCase
{
    #[Test]
    #[TestWith([null])]
    #[TestWith([true])]
    #[TestWith(['foo'])]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([[1 => ['wom', 'bat']]])]
    public function testExecute_NoStages(mixed $payload): void
    {
        $pipeline = new Pipeline();
        $result = $pipeline->execute($payload);

        if (is_object($payload)) { // pipeline will create a copy
            $this->assertEquals($payload, $result);
        } else {
            $this->assertSame($payload, $result);
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testExecute_AddStages(): array
    {
        return [
            [
                null,
                null,
            ],
            [
                true,
                10, // convert-bool-to-int -> multiply-by-10
            ],
            [
                'foo',
                'CONVERTED-foo', // prepend-string
            ],
            [
                '100',
                'CONVERTED-100', // prepend-string (changes from numeric, so no multiply)
            ],
            [
                42,
                420, // multiply-by-10
            ],
            [
                3.141,
                31.41, // multiply-by-10
            ],
            [
                ['foo' => 'bar'],
                ['foo' => 'bar'],
            ],
            [
                [1, 2, 3],
                [1, 2, 3], // is array, so no multiply
            ],
            [
                [1 => ['wom', 'bat']],
                [1 => ['wom', 'bat']],
            ],
            [
                new TestIterator([1 => ['wom', 'bat']]),
                new TestIterator([1 => ['wom', 'bat']]),
            ],
            [
                [1 => new TestIterator(['wom', 'bat'])],
                [1 => new TestIterator(['wom', 'bat'])],
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_AddStages')]
    public function testExecute_AddStages(mixed $payload, mixed $expectedResult): void
    {
        $pipeline = new Pipeline();
        $pipeline->addStage(
            stage: $this->getMockStage(static function (mixed $payload): mixed {
                return is_bool($payload)
                    ? (int)$payload
                    : $payload;
            }),
            identifier: 'convert-bool-to-int',
        );
        $pipeline->addStage(
            stage: $this->getMockStage(static function (mixed $payload): mixed {
                return is_string($payload)
                    ? 'CONVERTED-' . $payload
                    : $payload;
            }),
            // prepend-string : identifier omitted for test purposes
        );
        $pipeline->addStage(
            stage: $this->getMockStage(static function (mixed $payload): mixed {
                return is_numeric($payload)
                    ? $payload * 10
                    : $payload;
            }),
            identifier: 'multiply-by-10',
        );

        $result = $pipeline->execute($payload);

        $testStrict = match (true) {
            is_object($expectedResult) => false,
            is_array($expectedResult) => !array_filter(
                $expectedResult,
                static fn (mixed $item): bool => is_object($item),
            ),
            default => true,
        };

        if ($testStrict) {
            $this->assertSame($expectedResult, $result);
        } else {
            $this->assertEquals($expectedResult, $result);
        }
    }

    #[Test]
    #[TestWith([null, null])]
    #[TestWith(['foo', 'CONVERTED-foo'])] // prepend-string
    #[TestWith([['foo' => 'bar'], 'CONVERTED-{"foo":"bar"}'])] // prepend-string -> converted
    public function testExecute_InitWithStages(mixed $payload, mixed $expectedResult): void
    {
        $stages = [
            'encode-array' => $this->getMockStage(static function (mixed $payload): mixed {
                return is_array($payload)
                    ? json_encode($payload)
                    : $payload;
            }),
            $this->getMockStage(static function (mixed $payload): mixed {
                return is_string($payload)
                    ? 'CONVERTED-' . $payload
                    : $payload;
            }),
        ];
        $pipeline = new Pipeline(stages: $stages);

        $result = $pipeline->execute($payload);

        if (is_object($payload)) { // pipeline will create a copy
            $this->assertEquals($expectedResult, $result);
        } else {
            $this->assertSame($expectedResult, $result);
        }
    }

    /**
     * @param callable $transformation
     * @return PipelineInterface
     */
    private function getMockStage(callable $transformation): PipelineInterface
    {
        $mockStage = $this->getMockBuilder(PipelineInterface::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockStage->expects($this->once())
            ->method('execute')
            ->willReturnCallback($transformation);

        return $mockStage;
    }
}
