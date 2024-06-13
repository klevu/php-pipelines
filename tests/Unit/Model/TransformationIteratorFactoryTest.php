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

use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\Model\TransformationIteratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TransformationIteratorFactory::class)]
class TransformationIteratorFactoryTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testCreateFromSyntaxDeclaration_Valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                'Append("(static|\")\"", $accessor, 42, 3.14, ["foo", ["bar", "baz"]], true, null, [["wom", "bat"], "baz"])', // phpcs:ignore Generic.Files.LineLength.TooLong
                new TransformationIterator([
                    new Transformation(
                        transformerName: 'Append',
                        arguments: $argumentIteratorFactory->create([
                            '(static|")"',
                            new Extraction(accessor: 'accessor'),
                            42,
                            3.14,
                            [
                                'foo',
                                [
                                    'bar',
                                    'baz',
                                ],
                            ],
                            true,
                            null,
                            [
                                [
                                    'wom',
                                    'bat',
                                ],
                                'baz',
                            ],
                        ]),
                    ),
                ]),
            ],
            [
                'Trim|FormatNumber(2, $config.decimals_separator, "")|Prepend($config.currency.getCode())',
                new TransformationIterator([
                    new Transformation(
                        transformerName: 'Trim',
                        arguments: null,
                    ),
                    new Transformation(
                        transformerName: 'FormatNumber',
                        arguments: $argumentIteratorFactory->create([
                            2,
                            new Extraction(accessor: 'config.decimals_separator'),
                            '',
                        ]),
                    ),
                    new Transformation(
                        transformerName: 'Prepend',
                        arguments: $argumentIteratorFactory->create([
                            new Extraction(accessor: 'config.currency.getCode()'),
                        ]),
                    ),
                ]),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testCreateFromSyntaxDeclaration_Valid')]
    public function testCreateFromSyntaxDeclaration_Valid(
        string $syntaxDeclaration,
        TransformationIterator $expectedResult,
    ): void {
        $transformationIteratorFactory = new TransformationIteratorFactory();

        $actualResult = $transformationIteratorFactory->createFromSyntaxDeclaration(
            syntaxDeclaration: $syntaxDeclaration,
        );

        $this->assertEquals($expectedResult, $actualResult);
    }
}
