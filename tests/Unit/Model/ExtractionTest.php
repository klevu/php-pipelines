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

use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Extraction::class)]
class ExtractionTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testConstruct_Valid(): array
    {
        return [
            [
                null,
                null,
            ],
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
                new Extraction('#Matryoshka'),
                null,
            ],
            [
                null,
                new TransformationIterator([
                    new Transformation('Foo'),
                ]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Valid')]
    public function testConstruct_Valid(
        mixed $accessor,
        mixed $transformations,
    ): void {
        $extraction = new Extraction(
            accessor: $accessor, // @phpstan-ignore-line We are explicitly testing the TypeError
            transformations: $transformations, // @phpstan-ignore-line We are explicitly testing the TypeError
        );

        $this->assertSame($accessor, $extraction->accessor);
        $this->assertSame($transformations, $extraction->transformations);
    }

    #[Test]
    public function testConstruct_Valid_OptionalArgs(): void
    {
        $extraction = new Extraction('foo');

        $this->assertSame('foo', $extraction->accessor);
        $this->assertSame(null, $extraction->transformations);
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
            [42, null],
            [3.14, null],
            [['foo'], null],
            [(object)['foo'], null],
            [false, null],
            [$fileHandle, null],
            [null, 'foo'],
            [null, 42],
            [null, 3.14],
            [null, ['foo']],
            [null, (object)['foo']],
            [null, false],
            [null, $fileHandle],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testConstruct_Invalid')]
    public function testConstruct_Invalid(
        mixed $accessor,
        mixed $transformations,
    ): void {
        $this->expectException(\TypeError::class);

        new Extraction(
            accessor: $accessor, // @phpstan-ignore-line We are explicitly testing the TypeError
            transformations: $transformations, // @phpstan-ignore-line We are explicitly testing the TypeError
        );
    }
}
