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

trait DivideDataProviderTrait
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_ForDivideData(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                1,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::DIVIDE,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 1,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                1,
            ],
            [
                4,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::DIVIDE,
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
                        value: Operations::DIVIDE,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 0.5,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                9.0,
            ],
            [
                5,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::DIVIDE,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 3.9,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                1.2820512820512822,
            ],
            [
                5.67,
                $argumentIteratorFactory->create([
                    new Argument(
                        value: Operations::DIVIDE,
                        key: CalcTransformer::ARGUMENT_INDEX_OPERATION,
                    ),
                    new Argument(
                        value: 2.34,
                        key: CalcTransformer::ARGUMENT_INDEX_VALUE,
                    ),
                ]),
                null,
                2.4230769230769234,
            ],
        ];
    }
}
