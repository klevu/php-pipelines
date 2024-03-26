<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 * phpcs:disable SlevomatCodingStandard.Classes.ClassStructure.IncorrectGroupOrder
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Parser;

use Klevu\Pipelines\Exception\Syntax\InvalidSyntaxDeclarationException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\Extraction;
use Klevu\Pipelines\Model\Transformation;
use Klevu\Pipelines\Model\TransformationIterator;
use Klevu\Pipelines\Parser\ArgumentConverter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentConverter::class)]
class ArgumentConverterTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_String(): array
    {
        return [
            [
                '""',
                new Argument(''),
            ],
            [
                '"foo"',
                new Argument('foo'),
            ],
            [
                <<<'ARG'
                "foo\"bar"
                ARG,
                new Argument('foo"bar'),
            ],
            [
                <<<'ARG'
                "foo\\bar"
                ARG,
                new Argument('foo\bar'),
            ],
            [
                <<<'ARG'
                "foo\\"bar"
                ARG,
                new Argument('foo\"bar'),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_String')]
    public function testExecute_Valid_String(
        string $argument,
        Argument $expectedResult,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->assertEquals(
            $expectedResult,
            $argumentConverter->execute($argument),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_Invalid_String(): array
    {
        return [
            ['"foo'],
            ['"foo\"'],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Invalid_String')]
    public function testExecute_Invalid_String(
        string $argument,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $argumentConverter->execute($argument);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_Scalar(): array
    {
        return [
            [
                'false',
                new Argument(false),
            ],
            [
                'FALSE',
                new Argument(false),
            ],
            [
                ' False ',
                new Argument(false),
            ],
            [
                'true',
                new Argument(true),
            ],
            [
                'TRUE',
                new Argument(true),
            ],
            [
                ' True ',
                new Argument(true),
            ],
            [
                '',
                new Argument(null),
            ],
            [
                '   ',
                new Argument(null),
            ],
            [
                'null',
                new Argument(null),
            ],
            [
                'NULL',
                new Argument(null),
            ],
            [
                ' Null ',
                new Argument(null),
            ],
            [
                '42',
                new Argument(42),
            ],
            [
                ' 42 ',
                new Argument(42),
            ],
            [
                '3.14',
                new Argument(3.14),
            ],
            [
                ' 3.0 ',
                new Argument(3.0),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_Scalar')]
    public function testExecute_Valid_Scalar(
        string $argument,
        Argument $expectedResult,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->assertEquals(
            $expectedResult,
            $argumentConverter->execute($argument),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_Invalid_Scalar(): array
    {
        return [
            ['foo'],
            ['%null'],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Invalid_Scalar')]
    public function testExecute_Invalid_Scalar(
        string $argument,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $argumentConverter->execute($argument);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_Extraction(): array
    {
        return [
            // Without transformations
            [
                '$',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: null,
                    ),
                    key: null,
                ),
            ],
            [
                '$foo',
                new Argument(
                    value: new Extraction(
                        accessor: 'foo',
                        transformations: null,
                    ),
                    key: null,
                ),
            ],
            [
                '$foo()',
                new Argument(
                    new Extraction(
                        accessor: 'foo()',
                        transformations: null,
                    ),
                    key: null,
                ),
            ],
            [
                '$foo::bar()',
                new Argument(
                    new Extraction(
                        accessor: 'foo::bar()',
                        transformations: null,
                    ),
                    key: null,
                ),
            ],
            // With transformations; no arguments
            [
                '$|Trim',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$foo|Transform',
                new Argument(
                    new Extraction(
                        accessor: 'foo',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                ' $foo|bar|Baz_123() ',
                new Argument(
                    new Extraction(
                        accessor: 'foo',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'bar',
                                arguments: null,
                            ),
                            new Transformation(
                                transformerName: 'Baz_123',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$foo::bar()|bar()|Baz_123()',
                new Argument(
                    new Extraction(
                        accessor: 'foo::bar()',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'bar',
                                arguments: null,
                            ),
                            new Transformation(
                                transformerName: 'Baz_123',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            // With transformations; with arguments
            [
                '$a|Transform({ 123: 456 } )',
                new Argument(
                    value: new Extraction( // $a|Transform({ 123: 456 }) })
                        accessor: 'a',
                        transformations: new TransformationIterator([
                            new Transformation( // Transform({ 123: 456 }) })
                                transformerName: 'Transform',
                                arguments: new ArgumentIterator([
                                    new Argument( // { 123: 456 }
                                        value: new ArgumentIterator([
                                            new Argument(
                                                value: 456,
                                                key: 123,
                                            ),
                                        ]),
                                        key: 0,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$|Trim(" $")',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: ' $',
                                        key: 0,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$|Trim(" $")|Transform',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: ' $',
                                        key: 0,
                                    ),
                                ]),
                            ),
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$|Trim(" $")|Transform()',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: ' $',
                                        key: 0,
                                    ),
                                ]),
                            ),
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: null,
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$|Transform|Trim(" $")',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: null,
                            ),
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: ' $',
                                        key: 0,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$|Transform()|Trim(" $")',
                new Argument(
                    value: new Extraction(
                        accessor: null,
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: null,
                            ),
                            new Transformation(
                                transformerName: 'Trim',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: ' $',
                                        key: 0,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$foo|Transform("foo", true, null, 42, 3.14)',
                new Argument(
                    new Extraction(
                        accessor: 'foo',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: 'foo',
                                        key: 0,
                                    ),
                                    new Argument(
                                        value: true,
                                        key: 1,
                                    ),
                                    new Argument(
                                        value: null,
                                        key: 2,
                                    ),
                                    new Argument(
                                        value: 42,
                                        key: 3,
                                    ),
                                    new Argument(
                                        value: 3.14,
                                        key: 4,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$foo|Transform(["foo", true, null, 42, 3.14])',
                new Argument(
                    new Extraction(
                        accessor: 'foo',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: new ArgumentIterator([
                                            new Argument(
                                                value: 'foo',
                                                key: 0,
                                            ),
                                            new Argument(
                                                value: true,
                                                key: 1,
                                            ),
                                            new Argument(
                                                value: null,
                                                key: 2,
                                            ),
                                            new Argument(
                                                value: 42,
                                                key: 3,
                                            ),
                                            new Argument(
                                                value: 3.14,
                                                key: 4,
                                            ),
                                        ]),
                                        key: 0,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
            [
                '$foo|Transform([$bar::baz()|NestedTransform($foo, ["bar", "baz"], , false)|Trim, 3.14], 42)',
                new Argument(
                    new Extraction(
                        accessor: 'foo',
                        transformations: new TransformationIterator([
                            new Transformation(
                                transformerName: 'Transform',
                                arguments: new ArgumentIterator([
                                    new Argument(
                                        value: new ArgumentIterator([
                                            new Argument(
                                                value: new Extraction(
                                                    accessor: 'bar::baz()',
                                                    transformations: new TransformationIterator([
                                                        new Transformation(
                                                            transformerName: 'NestedTransform',
                                                            arguments: new ArgumentIterator([
                                                                new Argument(
                                                                    value: new Extraction(
                                                                        accessor: 'foo',
                                                                        transformations: null,
                                                                    ),
                                                                    key: 0,
                                                                ),
                                                                new Argument(
                                                                    value: new ArgumentIterator([
                                                                        new Argument(
                                                                            value: 'bar',
                                                                            key: 0,
                                                                        ),
                                                                        new Argument(
                                                                            value: 'baz',
                                                                            key: 1,
                                                                        ),
                                                                    ]),
                                                                    key: 1,
                                                                ),
                                                                new Argument(
                                                                    value: null,
                                                                    key: 2,
                                                                ),
                                                                new Argument(
                                                                    value: false,
                                                                    key: 3,
                                                                ),
                                                            ]),
                                                        ),
                                                        new Transformation(
                                                            transformerName: 'Trim',
                                                            arguments: null,
                                                        ),
                                                    ]),
                                                ),
                                                key: 0,
                                            ),
                                            new Argument(
                                                value: 3.14,
                                                key: 1,
                                            ),
                                        ]),
                                        key: 0,
                                    ),
                                    new Argument(
                                        value: 42,
                                        key: 1,
                                    ),
                                ]),
                            ),
                        ]),
                    ),
                    key: null,
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_Extraction')]
    public function testExecute_Valid_Extraction(
        string $argument,
        Argument $expectedResult,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->assertEquals(
            $expectedResult,
            $argumentConverter->execute($argument),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_Invalid_Extraction(): array
    {
        return [
            [
                '$foo|Tr im[]',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Invalid_Extraction')]
    public function testExecute_Invalid_Extraction(
        string $argument,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $argumentConverter->execute($argument);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_Array(): array
    {
        return [
            [
                '["foo", true, null, 3.14, 42]',
                new Argument(
                    value: new ArgumentIterator([
                        new Argument('foo'),
                        new Argument(true),
                        new Argument(null),
                        new Argument(3.14),
                        new Argument(42),
                    ]),
                    key: null,
                ),
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_Array')]
    public function testExecute_Valid_Array(
        string $argument,
        Argument $expectedResult,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->assertEquals(
            $expectedResult,
            $argumentConverter->execute($argument),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_Invalid_Array(): array
    {
        return [
            ['["foo", true, null, 3.14, 42'],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Invalid_Array')]
    public function testExecute_Invalid_Array(
        string $argument,
    ): void {
        $argumentConverter = new ArgumentConverter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $argumentConverter->execute($argument);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_Object(): array
    {
        return [
            [
                <<<'ARG'
                {"foo": "bar", "wom" : "bat", $a: $b}
                ARG,
                new Argument(
                    value: new ArgumentIterator([
                        new Argument(
                            value: 'bar',
                            key: 'foo',
                        ),
                        new Argument(
                            value: 'bat',
                            key: 'wom',
                        ),
                        new Argument(
                            value: new Extraction(
                                accessor: 'b',
                                transformations: null,
                            ),
                            key: new Extraction(
                                accessor: 'a',
                                transformations: null,
                            ),
                        ),
                    ]),
                ),
            ],
            [
                <<<'ARG'
                { "foo\":bar" : $a|Transform }
                ARG,
                new Argument(
                    value: new ArgumentIterator([
                        new Argument(
                            value: new Extraction(
                                accessor: 'a',
                                transformations: new TransformationIterator([
                                    new Transformation(
                                        transformerName: 'Transform',
                                        arguments: null,
                                    ),
                                ]),
                            ),
                            key: 'foo":bar',
                        ),
                    ]),
                    key: null,
                ),
            ],
            [
                <<<'ARG'
                { "foo\":bar" : $a|Transform({ 123: 456 }) }
                ARG,
                new Argument(
                    value: new ArgumentIterator([
                        new Argument(
                            value: new Extraction(
                                accessor: 'a',
                                transformations: new TransformationIterator([
                                    new Transformation(
                                        transformerName: 'Transform',
                                        arguments: new ArgumentIterator([
                                            new Argument( // { 123: 456 }
                                                value: new ArgumentIterator([
                                                    new Argument(
                                                        value: 456,
                                                        key: 123,
                                                    ),
                                                ]),
                                                key: 0,
                                            ),
                                        ]),
                                    ),
                                ]),
                            ),
                            key: 'foo":bar',
                        ),
                    ]),
                    key: null,
                ),
            ],
            // phpcs:disable Generic.Files.LineLength.TooLong
            [
                <<<'ARG'
                { "foo\":bar" : $a|Transform({ 123: 456, $config::getKey():[{$config::test: $|Trim},true] },)|FormatNumber($config::currency.decimal_places, "",),"foo": {"bar": [$baz]}}
                ARG,
                new Argument(
                    value: new ArgumentIterator([ // { "foo\":bar" : $a|Transform({ 123: 456, $config::getKey():[{$config::test: $|Trim},true] },)|FormatNumber($config::currency.decimal_places, "",),"foo": {"bar": [$baz]}}
                        new Argument( // "foo\":bar" : $a|Transform({ 123: 456, $config::getKey():[{$config::test: $|Trim},true] },)|FormatNumber($config::currency.decimal_places, "",)
                            value: new Extraction( // $a|Transform({ 123: 456, $config::getKey():[{$config::test: $|Trim},true] },)|FormatNumber($config::currency.decimal_places, "",)
                                accessor: 'a',
                                transformations: new TransformationIterator([
                                    new Transformation( // Transform({ 123: 456, $config::getKey():[{$config::test: $|Trim},true] },)
                                        transformerName: 'Transform',
                                        arguments: new ArgumentIterator([ // { 123: 456, $config::getKey():[{$config::test: $|Trim},true] },
                                            new Argument(
                                                value: new ArgumentIterator([ // { 123: 456, $config::getKey():[{$config::test: $|Trim},true] }
                                                    new Argument( // 123: 456
                                                        value: 456,
                                                        key: 123,
                                                    ),
                                                    new Argument( // $config::getKey():[{$config::test: $|Trim},true]
                                                        value: new ArgumentIterator([ // [{$config::test: $|Trim},true]
                                                            new Argument(
                                                                value: new ArgumentIterator([ // {$config::test: $|Trim}
                                                                    new Argument(
                                                                        value: new Extraction( // $|Trim
                                                                            accessor: null,
                                                                            transformations: new TransformationIterator([
                                                                                new Transformation(
                                                                                    transformerName: 'Trim',
                                                                                    arguments: null,
                                                                                ),
                                                                            ]),
                                                                        ),
                                                                        key: new Extraction(
                                                                            accessor: 'config::test',
                                                                            transformations: null,
                                                                        ),
                                                                    ),
                                                                ]),
                                                                key: 0,
                                                            ),
                                                            new Argument(
                                                                value: true,
                                                                key: 1,
                                                            ),
                                                        ]),
                                                        key: new Extraction(
                                                            accessor: 'config::getKey()',
                                                            transformations: null,
                                                        ),
                                                    ),
                                                ]),
                                                key: 0,
                                            ),
                                            new Argument(
                                                value: null,
                                                key: 1,
                                            ),
                                        ]),
                                    ),
                                    new Transformation( // FormatNumber($config::currency.decimal_places, "",)
                                        transformerName: 'FormatNumber',
                                        arguments: new ArgumentIterator([
                                            new Argument(
                                                value: new Extraction(
                                                    accessor: 'config::currency.decimal_places',
                                                    transformations: null,
                                                ),
                                                key: 0,
                                            ),
                                            new Argument(
                                                value: '',
                                                key: 1,
                                            ),
                                            new Argument(
                                                value: null,
                                                key: 2,
                                            ),
                                        ]),
                                    ),
                                ]),
                            ),
                            key: 'foo":bar',
                        ),
                        new Argument( // "foo": {"bar": [$baz]}
                            value: new ArgumentIterator([
                                new Argument(
                                    value: new ArgumentIterator([
                                        new Argument(
                                            value: new Extraction(
                                                accessor: 'baz',
                                                transformations: null,
                                            ),
                                            key: 0,
                                        ),
                                    ]),
                                    key: 'bar',
                                ),
                            ]),
                            key: 'foo',
                        ),
                    ]),
                    key: null,
                ),
            ],
            // phpcs:enable Generic.Files.LineLength.TooLong
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_Object')]
    public function testExecute_Valid_Object(
        string $argument,
        Argument $expectedResult,
    ): void {
        $argumentConverter = new ArgumentConverter();

        try {
            $this->assertEquals(
                $expectedResult,
                $argumentConverter->execute($argument),
            );
        } catch (InvalidSyntaxDeclarationException $e) {
            $this->fail(
                $e->getMessage() . ' - ' . $e->getSyntaxDeclaration(),
            );
        }
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Invalid_Object(): array
    {
        return [
            [
                'foo',
            ],
        ];
    }

    #[Test]
    #[DataProvider('dataProvider_testExecute_Invalid_Object')]
    public function testExecute_Invalid_Object(
        string $argument, // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    ): void {
        $this->markTestIncomplete('todo');
    }
}
