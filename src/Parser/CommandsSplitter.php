<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable SlevomatCodingStandard.Complexity.Cognitive.ComplexityTooHigh
 * @todo Reduce complexity
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Parser;

use Klevu\Pipelines\Exception\Syntax\InvalidSyntaxDeclarationException;

/**
 * @internal
 */
class CommandsSplitter
{
    /**
     * @param string|null $syntaxString
     * @return string[][]
     */
    public function execute(
        ?string $syntaxString,
    ): array {
        $syntaxString = trim((string)$syntaxString);
        if ('' === $syntaxString) {
            return [];
        }

        // Shortcut for performance
        //  If there are no parentheses at all, we can just explode on the splitter character
        if (!str_contains($syntaxString, '(')) {
            return $this->executeSimpleCommandsWithoutParentheses($syntaxString);
        }

        $commands = [];
        $currentCommand = [
            'command' => '',
            'arguments' => '',
        ];

        $inCommand = true;
        $inArguments = false;
        $inString = false;
        $lastEscapeCharacter = -1;
        $parenthesesDepth = 0;
        foreach (str_split($syntaxString) as $index => $currentCharacter) {
            if ($inCommand) {
                // Is it piping to a new command?
                if ('|' === $currentCharacter) {
                    // - Add to commands
                    $commands[] = $currentCommand;
                    // - Reset currentCommand
                    $currentCommand = [
                        'command' => '',
                        'arguments' => '',
                    ];
                    // - Mark that we are in command
                    $inCommand = true;
                    $inArguments = false;
                    // - Do not record character
                    continue;
                }

                // Is it a valid command character?
                if (
                    !$currentCommand['command']
                    && !ctype_alpha($currentCharacter)
                    && '\\' !== $currentCharacter
                ) {
                    throw new InvalidSyntaxDeclarationException(
                        syntaxDeclaration: $syntaxString,
                        message: sprintf(
                            'Invalid character encountered for command name at index %d: %s',
                            $index,
                            $currentCharacter,
                        ),
                    );
                }

                // Is it an opening parentheses?
                if ('(' === $currentCharacter) {
                    // - Mark that we are in arguments, not command
                    $inCommand = false;
                    $inArguments = true;
                    // - Update the parentheses depth (required to identify nested transforms / extractions)
                    $parenthesesDepth++;
                    // - Do not record character
                    continue;
                }

                if (!preg_match('/[\\\\a-zA-Z0-9_]/', $currentCharacter)) {
                    throw new InvalidSyntaxDeclarationException(
                        syntaxDeclaration: $syntaxString,
                        message: sprintf(
                            'Invalid character encountered for command name at index %d: %s',
                            $index,
                            $currentCharacter,
                        ),
                    );
                }

                $currentCommand['command'] .= $currentCharacter;
            }

            if ($inArguments) {
                // Are we currently in a string?
                if ($inString) {
                    // Are we closing this string?
                    if ('"' === $currentCharacter) {
                        if ($lastEscapeCharacter !== $index - 1) {
                            $inString = false;
                        }
                    }

                    // - Check if we are intending to escape the next character
                    if ('\\' === $currentCharacter) {
                        $lastEscapeCharacter = $index;
                    }

                    // - Ignore closing parentheses and pipes, etc
                    $currentCommand['arguments'] .= $currentCharacter;
                } else {
                    // Is this a closing parentheses?
                    if (')' === $currentCharacter) {
                        // Is this the last parentheses
                        if (0 === --$parenthesesDepth) {
                            // - Add to commands
                            $commands[] = $currentCommand;
                            // - Reset currentCommand
                            $currentCommand = [
                                'command' => '',
                                'arguments' => '',
                            ];
                            // - Mark that we're back into a command (pipes which follow parentheses get handled there)
                            $inCommand = true;
                            $inArguments = false;
                            continue;
                        }
                    }

                    // Is this a nested opening parentheses?
                    if ('(' === $currentCharacter) {
                        $parenthesesDepth++;
                    }

                    // Are starting a string?
                    if ('"' === $currentCharacter) {
                        $inString = true;
                    }

                    $currentCommand['arguments'] .= $currentCharacter;
                }
            }
        }

        // Check for parsing errors
        // - Any unclosed parentheses?
        if ($parenthesesDepth > 0) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $syntaxString,
                message: sprintf(
                    'Found %d unclosed parentheses characters',
                    $parenthesesDepth,
                ),
            );
        }

        // Clean up any hanging current command
        if ($currentCommand['command']) {
            $commands[] = $currentCommand;
        }

        // Clear any empty entries
        $commands = array_values(
            array_filter(
                $commands,
                static fn (array $command): bool => !!$command['command'],
            ),
        );

        return $commands;
    }

    /**
     * @param string $syntaxString
     * @return string[][]
     */
    private function executeSimpleCommandsWithoutParentheses(
        string $syntaxString,
    ): array {
        /** @var string[][] $commands */
        $commands = array_filter(
            array_map(
                static fn (string $command): array => [
                    'command' => $command,
                    'arguments' => '',
                ],
                explode('|', $syntaxString),
            ),
        );
        foreach ($commands as $command) {
            if (!preg_match('/^[\\\\a-zA-Z]([\\\\a-zA-Z_0-9])*$/', $command['command'])) {
                throw new InvalidSyntaxDeclarationException(
                    syntaxDeclaration: $syntaxString,
                    message: sprintf(
                        'Illegal characters found in command: %s',
                        $command['command'],
                    ),
                );
            }
        }

        return $commands;
    }
}
