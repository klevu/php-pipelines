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
use Klevu\Pipelines\Parser\CommandsSplitter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CommandsSplitter::class)]
class CommandsSplitterTest extends TestCase
{
    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_WithoutArguments(): array
    {
        return [
            [
                '',
                [],
            ],
            [
                'Transform',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
            [
                'Transform|Transform',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
            [
                'Transform()|Transform',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
            [
                'Transform|Transform()',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
            [
                'Transform()|Transform()',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string|null $syntaxString
     * @param mixed[][] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_WithoutArguments')]
    public function testExecute_WithoutArguments(
        ?string $syntaxString,
        array $expectedResult,
    ): void {
        $commandsSplitter = new CommandsSplitter();

        $this->assertSame(
            $expectedResult,
            $commandsSplitter->execute($syntaxString),
        );
    }

    /**
     * @return mixed[][]
     */
    public static function dataProvider_testExecute_WithSimpleArguments(): array
    {
        return [
            [
                'Transform($foo, "bar", 42, 3.14, null, false)',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '$foo, "bar", 42, 3.14, null, false',
                    ],
                ],
            ],
            [
                'Transform|Transform($foo, "bar", 42, 3.14, null, false)',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '$foo, "bar", 42, 3.14, null, false',
                    ],
                ],
            ],
            [
                'Transform($foo, "bar", 42, 3.14, null, false)|Transform',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '$foo, "bar", 42, 3.14, null, false',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],
            [
                'Transform($foo, "bar", 42, 3.14, null, false)|Transform($foo, "bar", 42, 3.14, null, false)',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '$foo, "bar", 42, 3.14, null, false',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '$foo, "bar", 42, 3.14, null, false',
                    ],
                ],
            ],

            [
                'Transform("foo()|bar", $foo()|bar)',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '"foo()|bar", $foo()|bar',
                    ],
                ],
            ],
            [
                'Transform()|Transform("foo()|bar", $foo()|bar)',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '"foo()|bar", $foo()|bar',
                    ],
                ],
            ],
            [
                'Transform("foo()|bar", $foo()|bar)|Transform()',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '"foo()|bar", $foo()|bar',
                    ],
                    [
                        'command' => 'Transform',
                        'arguments' => '',
                    ],
                ],
            ],

            [
                'Transform("foo\"bar")',
                [
                    [
                        'command' => 'Transform',
                        'arguments' => '"foo\"bar"',
                    ],
                ],
            ],
        ];
    }

    /**
     * @param string|null $syntaxString
     * @param mixed[][] $expectedResult
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_WithSimpleArguments')]
    public function testExecute_WithSimpleArguments(
        ?string $syntaxString,
        array $expectedResult,
    ): void {
        $commandsSplitter = new CommandsSplitter();

        $this->assertSame(
            $expectedResult,
            $commandsSplitter->execute($syntaxString),
        );
    }

    /**
     * @return string[][]
     */
    public static function dataProvider_testExecute_InvalidSyntax(): array
    {
        return [
            // Invalid command name without arguments
            ['1Transform'],
            ['_Transform'],
            ['!Transform'],
            ['Trans%form'],
            ['Trans|()'],
            ['Trans|1Transform()'],
            ['("foo")'],
            // Invalid command name with arguments
            ['1Transform()'],
            ['_Transform($foo)'],
            ['!Transform("bar", "baz")'],
            ['Trans%form()'],
            // Unclosed parameters
            ['Transform("foo", "bar"'],
            ['Transform("foo", $bar()'],
            // Too many closed parameters
            ['Transform("foo"))'],
        ];
    }

    /**
     * @param string|null $syntaxString
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testExecute_InvalidSyntax')]
    public function testExecute_InvalidSyntax(
        ?string $syntaxString,
    ): void {
        $commandsSplitter = new CommandsSplitter();

        $this->expectException(InvalidSyntaxDeclarationException::class);
        $commandsSplitter->execute($syntaxString);
    }
}
