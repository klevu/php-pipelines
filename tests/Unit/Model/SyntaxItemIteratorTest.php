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
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\SyntaxItem;
use Klevu\Pipelines\Model\SyntaxItemIterator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SyntaxItemIterator::class)]
class SyntaxItemIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @var string
     */
    protected string $iteratorFqcn = SyntaxItemIterator::class;
    /**
     * @var string
     */
    protected string $itemFqcn = SyntaxItem::class;

    /**
     * @return mixed[][][]
     */
    public static function dataProvider_valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new SyntaxItem('foo'),
                ],
            ],
            [
                [
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                        ]),
                    ),
                    new SyntaxItem('bar'),
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
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new SyntaxItem('foo'),
                    new SyntaxItem('bar'),
                ],
                static fn (SyntaxItem $argument): bool => $argument->command === 'foo',
                [
                    new SyntaxItem('foo'),
                ],
            ],
            [
                [
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                static fn (SyntaxItem $syntaxItem): bool => $syntaxItem->arguments?->count() > 1,
                [
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                ],
            ],
        ];
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_walk(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                static function (SyntaxItem &$syntaxItem): void {
                    if ($syntaxItem->arguments?->count() < 2) {
                        return;
                    }

                    $syntaxItem->arguments->addItem(
                        new Argument(
                            value: 'bat',
                            key: 'foo',
                        ),
                    );
                },
                [
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                            'foo' => 'bat',
                        ]),
                    ),
                    new SyntaxItem(
                        command: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
            ],
        ];
    }
}
