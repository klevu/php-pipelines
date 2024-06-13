<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Model;

use Klevu\Pipelines\Model\PipelineResult;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(PipelineResult::class)]
class PipelineResultTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testConstruct_Valid(): array
    {
        return [
            [true, null, []],
            [false, null, []],
            [true, 'foo', []],
            [true, 42, []],
            [true, 3.14, []],
            [true, true, []],
            [true, ['foo'], []],
            [true, (object)['foo' => 'bar'], []],
            [true, null, ['foo', 'bar']],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Valid')]
    public function testConstruct_Valid(
        mixed $success,
        mixed $payload,
        mixed $messages,
    ): void {
        $pipelineResult = new PipelineResult(
            success: $success, // @phpstan-ignore-line We are explicitly testing the TypeError
            payload: $payload,
            messages: $messages, // @phpstan-ignore-line We are explicitly testing the TypeError
        );

        $this->assertSame($success, $pipelineResult->success);
        $this->assertSame($payload, $pipelineResult->payload);
        $this->assertSame($messages, $pipelineResult->messages);
    }

    #[Test]
    public function testConstruct_Valid_OptionalArgs(): void
    {
        $pipelineResult = new PipelineResult(true);

        $this->assertSame(true, $pipelineResult->success);
        $this->assertSame(null, $pipelineResult->payload);
        $this->assertSame([], $pipelineResult->messages);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testConstruct_Invalid(): array
    {
        $fileHandle = fopen(
            filename: __FILE__,
            mode: 'r',
        );
        $fileHandle && fclose($fileHandle);

        return [
            [null, null, []],
            ['foo', null, []],
            [42, null, []],
            [3.14, null, []],
            [['foo'], null, []],
            [(object)['foo' => 'bar'], null, []],
            [$fileHandle, null, []],

            ['foo', null, null],
            ['foo', null, 'foo'],
            ['foo', null, 42],
            ['foo', null, 3.14],
            ['foo', null, (object)['foo' => 'bar']],
            ['foo', null, $fileHandle],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Invalid')]
    public function testConstruct_Invalid(
        mixed $success,
        mixed $payload,
        mixed $messages,
    ): void {
        $this->expectException(\TypeError::class);

        new PipelineResult(
            success: $success, // @phpstan-ignore-line We are explicitly testing the TypeError
            payload: $payload,
            messages: $messages, // @phpstan-ignore-line We are explicitly testing the TypeError
        );
    }
}
