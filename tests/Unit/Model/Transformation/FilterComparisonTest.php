<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Model\Transformation;

use Klevu\Pipelines\Model\Comparators;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation\FilterComparison;
use Klevu\Pipelines\Test\Fixture\TestIterator;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(FilterComparison::class)]
class FilterComparisonTest extends TestCase
{
    #[Test]
    #[TestWith([null, Comparators::EQUALS, null, false])]
    #[TestWith(['foo', Comparators::IN, new TestIterator(['foo', 'bar']), true])]
    #[TestWith([new Extraction(accessor: 'foo.getBar()'), Comparators::IN, new TestIterator(['foo', 'bar']), true])]
    #[TestWith([new Extraction(accessor: 'foo.getBar()'), Comparators::IN, new Extraction(accessor: 'config::getBar()'), true])] // phpcs:ignore Generic.Files.LineLength.TooLong
    public function testConstruct_Valid(
        mixed $sourceValue,
        mixed $comparator,
        mixed $compareValue,
        mixed $strict,
    ): void {
        $filterComparison = new FilterComparison(
            sourceValue: $sourceValue,
            comparator: $comparator,
            compareValue: $compareValue,
            strict: $strict,
        );

        $this->assertSame($sourceValue, $filterComparison->sourceValue);
        $this->assertSame($comparator, $filterComparison->comparator);
        $this->assertSame($compareValue, $filterComparison->compareValue);
        $this->assertSame($strict, $filterComparison->strict);
    }

    #[Test]
    #[TestWith([null, 'eq', null, false])]
    #[TestWith([null, Comparators::EQUALS, null, '1'])]
    public function testConstruct_Invalid(
        mixed $sourceValue,
        mixed $comparator,
        mixed $compareValue,
        mixed $strict,
    ): void {
        $this->expectException(\Error::class);

        // phpcs:ignore SlevomatCodingStandard.Variables.UnusedVariable.UnusedVariable
        $filterComparison = new FilterComparison(
            sourceValue: $sourceValue,
            comparator: $comparator,
            compareValue: $compareValue,
            strict: $strict,
        );
    }

    #[Test]
    public function testReadonly_SourceValue(): void
    {
        /** @noinspection PhpNamedArgumentsWithChangedOrderInspection */
        $filterComparison = new FilterComparison(
            strict: true,
            compareValue: ['bar'],
            comparator: Comparators::EQUALS,
            sourceValue: 'foo',
        );

        $this->expectException(\Error::class);
        /** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */
        $filterComparison->sourceValue = 'bar';
    }

    #[Test]
    public function testReadonly_Comparator(): void
    {
        $filterComparison = new FilterComparison(
            sourceValue: 'foo',
            comparator: Comparators::EQUALS,
            compareValue: ['bar'],
            strict: true,
        );

        $this->expectException(\Error::class);
        /** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */
        $filterComparison->comparator = Comparators::IN;
    }

    #[Test]
    public function testReadonly_CompareValue(): void
    {
        $filterComparison = new FilterComparison(
            sourceValue: 'foo',
            comparator: Comparators::EQUALS,
            compareValue: ['bar'],
            strict: true,
        );

        $this->expectException(\Error::class);
        /** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */
        $filterComparison->compareValue = null;
    }

    #[Test]
    public function testReadonly_Strict(): void
    {
        $filterComparison = new FilterComparison(
            sourceValue: 'foo',
            comparator: Comparators::EQUALS,
            compareValue: ['bar'],
            strict: true,
        );

        $this->expectException(\Error::class);
        /** @noinspection PhpReadonlyPropertyWrittenOutsideDeclarationScopeInspection */
        $filterComparison->strict = false;
    }
}
