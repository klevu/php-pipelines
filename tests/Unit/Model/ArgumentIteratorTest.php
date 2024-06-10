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
use Klevu\Pipelines\Test\Fixture\TestObject;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ArgumentIterator::class)]
class ArgumentIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @var string
     */
    protected string $iteratorFqcn = ArgumentIterator::class;
    /**
     * @var string
     */
    protected string $itemFqcn = Argument::class;

    /**
     * @return mixed[][][]
     */
    public static function dataProvider_valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new Argument('foo'),
                ],
            ],
            [
                [
                    new Argument(
                        value: 'foo',
                        key: 'bar',
                    ),
                    new Argument(
                        value: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                        ]),
                        key: new Extraction('getFoo()'),
                    ),
                ],
                [
                    new Argument(
                        value: false,
                        key: [
                            'foo' => new TestObject('bar'),
                            'baz',
                            'wom' => ['bat'],
                        ],
                    ),
                ],
            ],
        ];
    }

    /**
     * @return mixed[][][]
     */
    public static function dataProvider_invalid(): array
    {
        return [
            [
                [
                    (object)['foo' => 'bar'],
                ],
            ],
            [
                [
                    'foo',
                ],
            ],
            [
                [
                    12345,
                ],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_filter(): array
    {
        return [
            [
                [
                    new Argument('foo'),
                    new Argument('bar'),
                ],
                static fn (Argument $argument): bool => $argument->getValue() === 'foo',
                [
                    new Argument('foo'),
                ],
            ],
            [
                [
                    new Argument('foo', 123),
                    new Argument('bar', 789),
                ],
                static fn (Argument $argument): bool => $argument->getKey() > 500,
                [
                    new Argument('bar', 789),
                ],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_walk(): array
    {
        return [
            [
                [
                    new Argument('foo', 123),
                    new Argument('bar', 789),
                ],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                static function (Argument &$argument): void {
                    $key = $argument->getKey();
                    $value = $argument->getValue();

                    $argument->setKey($value);
                    $argument->setValue($key);
                },
                [
                    new Argument(123, 'foo'),
                    new Argument(789, 'bar'),
                ],
            ],
        ];
    }
}
