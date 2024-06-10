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
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(ValidationIterator::class)]
class ValidationIteratorTest extends AbstractIteratorTestCase
{
    /**
     * @var string
     */
    protected string $iteratorFqcn = ValidationIterator::class;
    /**
     * @var string
     */
    protected string $itemFqcn = Validation::class;

    /**
     * @return mixed[][][]
     */
    public static function dataProvider_valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                [
                    new Validation('foo'),
                ],
            ],
            [
                [
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                        ]),
                    ),
                    new Validation('bar'),
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
                    new Validation('foo'),
                    new Validation('bar'),
                ],
                static fn (Validation $argument): bool => $argument->validatorName === 'foo',
                [
                    new Validation('foo'),
                ],
            ],
            [
                [
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                static fn (Validation $validation): bool => $validation->arguments?->count() > 1,
                [
                    new Validation(
                        validatorName: 'foo',
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
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                        ]),
                    ),
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
                // phpcs:ignore SlevomatCodingStandard.PHP.DisallowReference.DisallowedPassingByReference
                static function (Validation &$validation): void {
                    if ($validation->arguments?->count() < 2) {
                        return;
                    }

                    $validation->arguments->addItem(
                        new Argument(
                            value: 'bat',
                            key: 'foo',
                        ),
                    );
                },
                [
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            'wom' => 'bat',
                            'a' => 'b',
                            'foo' => 'bat',
                        ]),
                    ),
                    new Validation(
                        validatorName: 'foo',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
                        ]),
                    ),
                ],
            ],
        ];
    }
}
