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

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Test\Fixture\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(Argument::class)]
class ArgumentTest extends TestCase
{
    #[Test]
    #[TestWith([null])]
    #[TestWith(['foo'])]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith([true])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([new \stdClass()])]
    #[TestWith([new Extraction('foo')])]
    public function testGetSetValue(
        mixed $value,
    ): void {
        $argument = new Argument(
            value: null,
        );

        $this->assertNull($argument->getKey());

        $argument->setValue($value);
        $this->assertSame($value, $argument->getValue());
    }

    #[Test]
    #[TestWith([null])]
    #[TestWith(['foo'])]
    #[TestWith([42])]
    #[TestWith([3.14])]
    #[TestWith([true])]
    #[TestWith([['foo' => 'bar']])]
    #[TestWith([new \stdClass()])]
    #[TestWith([new Extraction('foo')])]
    public function testGetSetKey(
        mixed $key,
    ): void {
        $argument = new Argument(
            value: null,
        );

        $this->assertNull($argument->getKey());

        $argument->setKey($key);
        $this->assertSame($key, $argument->getKey());
    }

    #[Test]
    #[TestWith([null, 'foo'])]
    #[TestWith(['foo', 'bar'])]
    #[TestWith([42, 3.14])]
    #[TestWith([3.14, false])]
    #[TestWith([true, ['wom' => 'bat']])]
    #[TestWith([['foo' => 'bar'], new \stdClass()])]
    #[TestWith([new \stdClass(), new TestObject('foo')])]
    #[TestWith([new Extraction('foo'), 'abc'])]
    public function testConstruct(
        mixed $value,
        mixed $key,
    ): void {
        $argument = new Argument(
            value: $value,
            key: $key,
        );

        $this->assertSame($value, $argument->getValue());
        $this->assertSame($key, $argument->getKey());
    }
}
