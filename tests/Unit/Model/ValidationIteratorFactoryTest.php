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
use Klevu\Pipelines\Model\Validation;
use Klevu\Pipelines\Model\ValidationIterator;
use Klevu\Pipelines\Model\ValidationIteratorFactory;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ValidationIteratorFactory::class)]
class ValidationIteratorFactoryTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testCreateFromSyntaxDeclaration_Valid(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                'CompositeOr(["IsNotEmpty", "DoesNotMatchRegex(\'/foo/\')"])', // phpcs:ignore Generic.Files.LineLength.TooLong
                new ValidationIterator([
                    new Validation(
                        validatorName: 'CompositeOr',
                        arguments: $argumentIteratorFactory->create([
                            [
                                0 => 'IsNotEmpty',
                                1 => "DoesNotMatchRegex('/foo/')",
                            ],
                        ]),
                    ),
                ]),
            ],
            [
                'IsNotEmpty|IsNotGreaterThan(6)|IsPositiveNumber(true)',
                new ValidationIterator([
                    new Validation(
                        validatorName: 'IsNotEmpty',
                        arguments: null,
                    ),
                    new Validation(
                        validatorName: 'IsNotGreaterThan',
                        arguments: $argumentIteratorFactory->create([
                            0 => 6,
                        ]),
                    ),
                    new Validation(
                        validatorName: 'IsPositiveNumber',
                        arguments: $argumentIteratorFactory->create([
                            0 => true,
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
        ValidationIterator $expectedResult,
    ): void {
        $validationIteratorFactory = new ValidationIteratorFactory();

        $actualResult = $validationIteratorFactory->createFromSyntaxDeclaration(
            syntaxDeclaration: $syntaxDeclaration,
        );

        $this->assertEquals($expectedResult, $actualResult);
    }
}
