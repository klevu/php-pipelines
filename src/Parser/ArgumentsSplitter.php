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
class ArgumentsSplitter
{
    /**
     * @param string|null $argumentsSyntaxString
     * @return string[]
     */
    public function execute(
        ?string $argumentsSyntaxString,
    ): array {
        $argumentsSyntaxString = trim((string)$argumentsSyntaxString);
        if ('' === $argumentsSyntaxString) {
            return [];
        }

        // Shortcut for performance
        //  If there are no strings, arrays. or nested transformations, we can just explode on the separator character
        if (!preg_match('/["()\[\]{}]/', $argumentsSyntaxString)) {
            return array_map(
                'trim',
                explode(',', $argumentsSyntaxString),
            );
        }

        $arguments = [];
        $currentArgument = '';

        $inString = false;
        $lastEscapeCharacter = -1;
        $parenthesesDepth = 0;
        $arrayDepth = 0;
        $objectDepth = 0;
        foreach (str_split($argumentsSyntaxString) as $index => $currentCharacter) {
            // Are we currently in a string?
            if ($inString) {
                // Are we closing this string?
                if ('"' === $currentCharacter) {
                    if ($lastEscapeCharacter !== $index - 1) {
                        $inString = false;
                    }
                }

                // Are we intending to escape the next character?
                if ('\\' === $currentCharacter) {
                    $lastEscapeCharacter = $index;
                }

                $currentArgument .= $currentCharacter;
                continue;
            }

            switch ($currentCharacter) {
                case '(':
                    $parenthesesDepth++;
                    break;
                case ')':
                    $parenthesesDepth--;
                    break;
                case '[':
                    $arrayDepth++;
                    break;
                case ']':
                    $arrayDepth--;
                    break;
                case '{':
                    $objectDepth++;
                    break;
                case '}':
                    $objectDepth--;
                    break;
            }

            // Are we in a chained call with its own arguments, an array, or an object?
            if ($parenthesesDepth || $arrayDepth || $objectDepth) {
                $currentArgument .= $currentCharacter;
                continue;
            }

            // Are starting a new string?
            if ('"' === $currentCharacter) {
                $inString = true;
                $currentArgument .= $currentCharacter;
                continue;
            }

            if (',' === $currentCharacter) {
                // A comma when we're inside a complex object is not an argument separator
                // Add to the list of found argument string
                $arguments[] = $currentArgument;
                // Reset the current argument
                $currentArgument = '';
                // Don't record the separator
                continue;
            }

            $currentArgument .= $currentCharacter;
        }

        // Check for parsing errors
        if ($inString) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argumentsSyntaxString,
                message: 'Found unclosed string in arguments',
            );
        }
        if ($parenthesesDepth > 0) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argumentsSyntaxString,
                message: sprintf(
                    'Found %d unclosed parentheses in arguments',
                    $parenthesesDepth,
                ),
            );
        }
        if ($arrayDepth > 0) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argumentsSyntaxString,
                message: sprintf(
                    'Found %d unclosed arrays in arguments',
                    $arrayDepth,
                ),
            );
        }
        if ($objectDepth > 0) {
            throw new InvalidSyntaxDeclarationException(
                syntaxDeclaration: $argumentsSyntaxString,
                message: sprintf(
                    'Found %d unclosed objects in arguments',
                    $objectDepth,
                ),
            );
        }

        // Add the hanging argument
        $arguments[] = $currentArgument;

        return array_map(
            'trim',
            $arguments,
        );
    }

    /**
     * @param string $string
     * @return array<string|null>
     */
    public function splitKeyValueString(string $string): array
    {
        if (!str_contains($string, ':')) {
            return [
                'key' => null,
                'value' => $string,
            ];
        }

        if (
            !str_contains($string, '"') // Strings may contain :
            && !str_contains($string, '::') // As may extractions
            && !str_contains($string, '{') // Or the string may have nested objects
        ) {
            $parts = array_map(
                'trim',
                explode(':', $string, 2),
            );

            return [
                'key' => $parts[0],
                'value' => $parts[1],
            ];
        }

        $inString = false;
        $inExtraction = false;
        $objectDepth = 0;
        $lastEscapeCharacter = -1;
        $separatorFoundAt = null;
        foreach (str_split($string) as $index => $currentCharacter) {
            if ($inString) {
                if ('"' === $currentCharacter) {
                    if ($lastEscapeCharacter !== $index - 1) {
                        $inString = false;
                    }
                }

                // Are we intending to escape the next character?
                if ('\\' === $currentCharacter) {
                    $lastEscapeCharacter = $index;
                }
                continue;
            }

            if ('{' === $currentCharacter) {
                $objectDepth++;
            }
            if ('}' === $currentCharacter) {
                $objectDepth--;
            }

            if ('"' === $currentCharacter) {
                $inString = true;
                continue;
            }

            if (':' === $currentCharacter && ':' === ($string[$index + 1] ?? null)) {
                $inExtraction = true;
            }

            if (
                ':' === $currentCharacter
                && !$inExtraction
                && 0 === $objectDepth
            ) {
                $separatorFoundAt = $index;
                break;
            }

            if ($inExtraction && ':' === $currentCharacter && ':' === $string[$index - 1]) {
                $inExtraction = false;
            }
        }

        if (null === $separatorFoundAt) {
            return [
                'key' => null,
                'value' => $string,
            ];
        }

        return array_map(
            'trim',
            [
                'key' => substr($string, 0, $separatorFoundAt),
                'value' => substr($string, $separatorFoundAt + 1),
            ],
        );
    }
}
