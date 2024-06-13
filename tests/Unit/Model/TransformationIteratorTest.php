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
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(TransformationIterator::class)]
class TransformationIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @var string
     */
    protected string $iteratorFqcn = TransformationIterator::class;
    /**
     * @var string
     */
    protected string $itemFqcn = Transformation::class;

    /**
     * @return mixed[][][]
     */
    public static function dataProvider_valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new Transformation('foo'),
                ],
            ],
            [
                [
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                        ]),
                    ),
                    new Transformation('bar'),
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
                    new Transformation('foo'),
                    new Transformation('bar'),
                ],
                static fn (Transformation $argument): bool => $argument->transformerName === 'foo',
                [
                    new Transformation('foo'),
                ],
            ],
            [
                [
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                static fn (Transformation $transformation): bool => $transformation->arguments?->count() > 1,
                [
                    new Transformation(
                        transformerName: 'foo',
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
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                static function (Transformation &$transformation): void {
                    if ($transformation->arguments?->count() < 2) {
                        return;
                    }

                    $transformation->arguments->addItem(
                        new Argument(
                            value: 'bat',
                            key: 'foo',
                        ),
                    );
                },
                [
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                            'foo' => 'bat',
                        ]),
                    ),
                    new Transformation(
                        transformerName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
            ],
        ];
    }
}
