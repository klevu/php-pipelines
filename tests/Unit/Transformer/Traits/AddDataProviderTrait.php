<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer\Traits;

use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Transformation\Calc\Operations;
use Klevu\Pipelines\Transformer\Calc as CalcTransformer;

trait AddDataProviderTrait
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForAddData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                7,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                9,
            ],
            [
                0,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                2,
            ],
            [
                4.5,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 0.5,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                5.0,
            ],
            [
                5,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 3.9,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                8.9,
            ],
            [
                1.25,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 1.81,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                3.06,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransformInvalid_ForAddData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                ['argument'],
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                new \stdClass(),
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                false,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 0.5,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                ' someString',
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 3.9,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                new Argument([]),
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::ADD,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 1.81,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
        ];
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransformInvalid_ArgumentsData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                9,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: 'false',
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                9,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: 'true',
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 0.5,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
            [
                9,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: 'add',
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 3.9,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
            ],
        ];
    }
}
