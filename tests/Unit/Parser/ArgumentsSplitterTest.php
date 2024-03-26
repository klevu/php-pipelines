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
use Klevu\Pipelines\Parser\ArgumentsSplitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ArgumentsSplitter::class)]
class ArgumentsSplitterTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_SimpleArguments_NoSpecialCharacters(): array
    {
        return [
            [
                '',
                [],
            ],
            [
                ',',
                [
                    '',
                    '',
                ],
            ],
            [
                'null',
                [
                    'null',
                ],
            ],
            [
                '$foo, 42, 3.14, null, false',
                [
                    '$foo',
                    '42',
                    '3.14',
                    'null',
                    'false',
                ],
            ],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @param string[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_SimpleArguments_NoSpecialCharacters')]
    public function testExecute_SimpleArguments_NoSpecialCharacters(
        ?string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_SimpleArguments_Strings(): array
    {
        return [
            [
                '"foo"',
                [
                    '"foo"',
                ],
            ],
            [
                '"foo\"bar"',
                [
                    '"foo\"bar"',
                ],
            ],
            [
                '"foo","bar"',
                [
                    '"foo"',
                    '"bar"',
                ],
            ],
            [
                '"foo", bar',
                [
                    '"foo"',
                    'bar',
                ],
            ],
            [
                '  " foo " ,  ba r , ',
                [
                    '" foo "',
                    'ba r',
                    '',
                ],
            ],
            [
                '"$foo()|bar({\"wom\": true, \"bat\" : false})[0] => \"||\"", "[a, b, c]"',
                [
                    '"$foo()|bar({\"wom\": true, \"bat\" : false})[0] => \"||\""',
                    '"[a, b, c]"',
                ],
            ],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @param string[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_SimpleArguments_Strings')]
    public function testExecute_SimpleArguments_Strings(
        ?string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_SimpleArguments_Arrays(): array
    {
        return [
            [
                '[1, 2, 3]',
                [
                    '[1, 2, 3]',
                ],
            ],
            [
                '["foo", "bar", "baz"]',
                [
                    '["foo", "bar", "baz"]',
                ],
            ],
            [
                '  [1, 2, 3]  , ["foo", "bar", "baz"],',
                [
                    '[1, 2, 3]',
                    '["foo", "bar", "baz"]',
                    '',
                ],
            ],
            [
                '[[1, 2, 3], ["foo", "bar", "baz"]], [[["wom"], "bat"], "bux"]',
                [
                    '[[1, 2, 3], ["foo", "bar", "baz"]]',
                    '[[["wom"], "bat"], "bux"]',
                ],
            ],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @param string[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_SimpleArguments_Arrays')]
    public function testExecute_SimpleArguments_Arrays(
        ?string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_SimpleArguments_Objects(): array
    {
        return [
            [
                '{"foo": "bar", "baz" : 42}',
                [
                    '{"foo": "bar", "baz" : 42}',
                ],
            ],
            [
                '{"foo": ["bar\"baz", 42]}',
                [
                    '{"foo": ["bar\"baz", 42]}',
                ],
            ],
            [
                '"foo" , {  "bar"  : "baz"  }, [  { "wom": "bat"} , 3.14],',
                [
                    '"foo"',
                    '{  "bar"  : "baz"  }',
                    '[  { "wom": "bat"} , 3.14]',
                    '',
                ],
            ],
            [
                '{$foo: $bar}',
                [
                    '{$foo: $bar}',
                ],
            ],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @param mixed[][] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_SimpleArguments_Objects')]
    public function testExecute_SimpleArguments_Objects(
        ?string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_SimpleArguments_Commands(): array
    {
        return [
            [
                '$foo|Transform, $config::bar|NumberFormat(2, ".", $config::baz|ToString|Trim("\" ", "end"))',
                [
                    '$foo|Transform',
                    '$config::bar|NumberFormat(2, ".", $config::baz|ToString|Trim("\" ", "end"))',
                ],
            ],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @param mixed[][] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_SimpleArguments_Commands')]
    public function testExecute_SimpleArguments_Commands(
        ?string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_InvalidSyntax(): array
    {
        return [
            // Unclosed string
            ['"foo", "bar, 42'],
            // Unclosed parentheses
            ['$foo|Trim(""'],
            ['[$foo|Trim(]'],
            ['{"foo": $config::getBar(}'],
            // Unclosed array
            ['[1, 2, 3'],
            ['[1, 2, ["foo", $bar]'],
            // Unclosed object
            ['{"foo": "bar"'],
            ['["foo", {"bar": "baz", 42]'],
        ];
    }

    /**
     * @param string|null $argumentsSyntaxString
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_InvalidSyntax')]
    public function testExecute_InvalidSyntax(
        ?string $argumentsSyntaxString,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $argumentsSplitter->execute($argumentsSyntaxString);
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_Valid_Objects(): array
    {
        return [
            [
                '{"foo": "bar"}',
                [
                    '{"foo": "bar"}',
                ],
            ],
            [
                '{"foo": "bar", "a": 123}',
                [
                    '{"foo": "bar", "a": 123}',
                ],
            ],
            [
                '{"foo": "bar", "a": 123}, {"wom": "bat", 456: "b"}',
                [
                    '{"foo": "bar", "a": 123}',
                    '{"wom": "bat", 456: "b"}',
                ],
            ],
        ];
    }

    /**
     * @param string $argumentsSyntaxString
     * @param mixed[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_Valid_Objects')]
    public function testExecute_Valid_Objects(
        string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->execute($argumentsSyntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testSplitKeyValueString_Valid(): array
    {
        return [
            [
                '',
                [
                    'key' => null,
                    'value' => '',
                ],
            ],
            [
                'foo',
                [
                    'key' => null,
                    'value' => 'foo',
                ],
            ],
            [
                '"foo:bar"',
                [
                    'key' => null,
                    'value' => '"foo:bar"',
                ],
            ],
            [
                '"foo": "bar"',
                [
                    'key' => '"foo"',
                    'value' => '"bar"',
                ],
            ],
            [
                '123: 456',
                [
                    'key' => '123',
                    'value' => '456',
                ],
            ],
            [
                '$foo::getBar() : 123',
                [
                    'key' => '$foo::getBar()',
                    'value' => '123',
                ],
            ],
            [
                '$foo::getBar()|Transform(":", "\"foo") : $bar|Transform()',
                [
                    'key' => '$foo::getBar()|Transform(":", "\"foo")',
                    'value' => '$bar|Transform()',
                ],
            ],
            [
                '$a|Transform({ 123: 456 })',
                [
                    'key' => null,
                    'value' => '$a|Transform({ 123: 456 })',
                ],
            ],
        ];
    }

    /**
     * @param string $argumentsSyntaxString
     * @param mixed[] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testSplitKeyValueString_Valid')]
    public function testSplitKeyValueString_Valid(
        string $argumentsSyntaxString,
        array $expectedResult,
    ): void {
        $argumentsSplitter = new ArgumentsSplitter();

        $this->assertSame(
            $expectedResult,
            $argumentsSplitter->splitKeyValueString($argumentsSyntaxString),
        );
    }
}
