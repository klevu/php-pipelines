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

trait SubtractDataProviderTrait
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForSubtractData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                7,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::SUBTRACT,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                5,
            ],
            [
                0,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::SUBTRACT,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                -2,
            ],
            [
                4.5,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::SUBTRACT,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 0.5,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                4.0,
            ],
            [
                5,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::SUBTRACT,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 3.9,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                1.1,
            ],
            [
                1.25,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::SUBTRACT,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 1.81,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                -0.56,
            ],
        ];
    }
}
