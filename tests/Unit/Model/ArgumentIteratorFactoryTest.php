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
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Extraction;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentIteratorFactory::class)]
class ArgumentIteratorFactoryTest extends TestCase
{
    #[Test]
    public function testCreate_Simple(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $expectedResult = new ArgumentIterator([
            new Argument(value: 'foo'),
        ]);
        $actualResult = $argumentIteratorFactory->create([
            'foo',
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function testCreate_Array(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $expectedResult = new ArgumentIterator([
            new Argument(
                value: new ArgumentIterator([
                    new Argument(value: 'foo'),
                    new Argument(value: 'bar'),
                ]),
            ),
        ]);
        $actualResult = $argumentIteratorFactory->create([
            [
                'foo',
                'bar',
            ],
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function testCreate_Hash(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $expectedResult = new ArgumentIterator([
            new Argument(
                value: new ArgumentIterator([
                    new Argument(
                        value: 'bar',
                        key: 'foo',
                    ),
                    new Argument(
                        value: 'baz',
                        key: 0,
                    ),
                    new Argument(
                        value: 'bat',
                        key: 'wom',
                    ),
                ]),
            ),
        ]);
        $actualResult = $argumentIteratorFactory->create([
            [
                'foo' => 'bar',
                'baz',
                'wom' => 'bat',
            ],
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function testCreate_NestedArrays(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $expectedResult = new ArgumentIterator([
            new Argument(
                value: new ArgumentIterator([
                    new Argument(
                        value: 'bar',
                        key: 'foo',
                    ),
                    new Argument(
                        value: new ArgumentIterator([
                            new Argument(
                                value: 'baz',
                                key: 0,
                            ),
                            new Argument(
                                value: 'bat',
                                key: 'wom',
                            ),
                        ]),
                        key: 0,
                    ),
                ]),
            ),
        ]);
        $actualResult = $argumentIteratorFactory->create([
            [
                'foo' => 'bar',
                [
                    'baz',
                    'wom' => 'bat',
                ],
            ],
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function testCreate_WithArgument(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $expectedResult = new ArgumentIterator([
            new Argument(
                value: new Extraction('foo'),
                key: new Extraction('bar'),
            ),
        ]);
        $actualResult = $argumentIteratorFactory->create([
            new Argument(
                value: new Extraction('foo'),
                key: new Extraction('bar'),
            ),
        ]);

        $this->assertEquals($expectedResult, $actualResult);
    }

    #[Test]
    public function testCreate_Idempotency(): void
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        $fixture = new ArgumentIterator([
            new Argument(
                value: new ArgumentIterator([
                    new Argument(
                        value: 'bar',
                        key: 'foo',
                    ),
                    new Argument(
                        value: new ArgumentIterator([
                            new Argument(
                                value: 'baz',
                                key: 0,
                            ),
                            new Argument(
                                value: 'bat',
                                key: 'wom',
                            ),
                        ]),
                        key: 0,
                    ),
                ]),
            ),
            new Argument(
                value: new Extraction('foo'),
                key: new Extraction('bar'),
            ),
        ]);

        $this->assertEquals(
            $fixture,
            $argumentIteratorFactory->create($fixture->toArray()),
        );
    }
}
