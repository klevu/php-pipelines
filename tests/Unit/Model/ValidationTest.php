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

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Validation;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Validation::class)]
class ValidationTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testConstruct_Valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                'foo',
                null,
            ],
            [
                // This (should) be invalid when executed, but from a data standpoint it's fine
                '<strong>&amp;$...!->()',
                null,
            ],
            [
                'foo',
                $argumentIteratorFactory->create([
                    'foo' => 'bar',
                    'wom' => 'bat',
                ]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Valid')]
    public function testConstruct_Valid(
        mixed $validatorName,
        mixed $arguments,
    ): void {
        $validation = new Validation(
            validatorName: $validatorName, // @phpstan-ignore-line We are explicitly testing the TypeError
            arguments: $arguments, // @phpstan-ignore-line We are explicitly testing the TypeError
        );

        $this->assertSame($validatorName, $validation->validatorName);
        $this->assertSame($arguments, $validation->arguments);
    }

    #[Test]
    public function testConstruct_Valid_OptionalArgs(): void
    {
        $validation = new Validation('foo');

        $this->assertSame('foo', $validation->validatorName);
        $this->assertSame(null, $validation->arguments);
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
            [null, null],
            [new Validation('foo'), null],
            [42, null],
            [3.14, null],
            [['foo'], null],
            [(object)['foo'], null],
            [false, null],
            [$fileHandle, null],
            ['foo', 'foo'],
            ['foo', 42],
            ['foo', 3.14],
            ['foo', ['foo']],
            ['foo', (object)['foo']],
            ['foo', false],
            ['foo', $fileHandle],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Invalid')]
    public function testConstruct_Invalid(
        mixed $validatorName,
        mixed $arguments,
    ): void {
        $this->expectException(\TypeError::class);

        new Validation(
            validatorName: $validatorName, // @phpstan-ignore-line We are explicitly testing the TypeError
            arguments: $arguments, // @phpstan-ignore-line We are explicitly testing the TypeError
        );
    }
}
