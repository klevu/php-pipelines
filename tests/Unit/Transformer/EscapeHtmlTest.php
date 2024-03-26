<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Transformer;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use Klevu\Pipelines\Model\Argument;
use Klevu\Pipelines\Model\ArgumentIterator;
use Klevu\Pipelines\Model\ArgumentIteratorFactory;
use Klevu\Pipelines\Transformer\EscapeHtml;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EscapeHtml::class)]
class EscapeHtmlTest extends TestCase
{
    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_Success(): array
    {
        return [
            [null, null],
            [true, '1'],
            [true, "1"],
            [false, ''],
            [false, ""],
            ['null', 'null'],
            ['', ''],
            [' ', ' '],
            ["<a href='test'>Test</a>", "&lt;a href='test'&gt;Test&lt;/a&gt;"],
            [
                "<strong>&quot;strong & escape&quot;</strong>",
                "&lt;strong&gt;&quot;strong &amp; escape&quot;&lt;/strong&gt;",
            ],
            [
                '<b>&quot;strong & single escape&quot;</b>',
                '&lt;b&gt;&quot;strong &amp; single escape&quot;&lt;/b&gt;',
            ],
            ["<p>Hello\s world</p>", '&lt;p&gt;Hello\s world&lt;/p&gt;'],
            ["Hello\"s'world", 'Hello&quot;s\'world'],
            ["Let's", 'Let\'s'],
            ["&", '&amp;'],
            ["&amp;", '&amp;'],
            ['"', '&quot;'],
            ['&quot;', '&quot;'],
            ["'", "'"], //TODO:/ Can't be this &apos;
            ['&apos;', '&apos;'],
            ['>', '&gt;'],
            ['&gt;', '&gt;'],
            ['<', '&lt;'],
            ['&lt;', '&lt;'],
            ['&excl;', '&amp;excl;'],
            ['&dollar;', '&amp;dollar;'],
            ['&euro;', '&amp;euro;'],
            //['+', '&plus;'],
            //['&plus;', '&plus;'],
            //[',', '&comma;'],
            //['&comma;', '&comma;'],
            //['!', '&excl;'],
            //['$', '&dollar;'],
            //['€', '&euro;'],

        ];
    }

    /**
     * @return string
     */
    public function getHeredocString(): string
    {
        return <<<'IDENTIFIER'
nowdoc.

NL also is preserved.
Single ' and " and , and &.
IDENTIFIER;
    }

    /**
     * @return void
     */
    #[Test]
    public function testTransform_WithHereDocSuccess(): void
    {
        $escapeHtmlTransformer = new EscapeHtml();
        $arguments = new ArgumentIterator([
            new Argument(
                value: false,
                key: EscapeHtml::ARGUMENT_ALLOW_DOUBLE_ENCODING,
            ),
        ]);

        $expectedResult = "nowdoc.\n\nNL also is preserved.\nSingle ' and &quot; and , and &amp;.";

        $result = $escapeHtmlTransformer->transform(
            data: $this->getHeredocString(),
            arguments: $arguments,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @param mixed $data
     * @param mixed $expectedResult
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_Success')]
    public function testTransform_WithSuccess(
        mixed $data,
        mixed $expectedResult,
    ): void {
        $escapeHtmlTransformer = new EscapeHtml();

        $arguments = new ArgumentIterator([
            new Argument(
                value: false,
                key: EscapeHtml::ARGUMENT_ALLOW_DOUBLE_ENCODING,
            ),
        ]);

        $result = $escapeHtmlTransformer->transform(
            data: $data,
            arguments: $arguments,
        );
        $this->assertSame($expectedResult, $result);
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidTransformationData_Exception(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                false,
                $argumentIteratorFactory->create([
                    ['', 'noEmpty'],
                ]),
                null,
            ],
            [
                true,
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
            [
                [true],
                $argumentIteratorFactory->create([
                    ['', 'nEmpty'],
                ]),
                null,
            ],
            [
                ['array'],
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
            [
                [' '],
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
            [
                [1],
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidTransformationData_Exception')]
    public function testTransform_WithInvalidTransformationData_Exception(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $escapeHtmlTransformer = new EscapeHtml();

        $this->expectException(InvalidTransformationArgumentsException::class);
        $this->expectExceptionMessage('Invalid argument for transformation');
        try {
            $escapeHtmlTransformer->transform(
                data: $input,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid Quotes argument \(\d+\) : .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: EscapeHtml::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }

    /**
     * @return mixed[]
     */
    public static function dataProvider_testTransform_InvalidData_Exception(): array
    {
        $argumentIteratorFactory = new ArgumentIteratorFactory();

        return [
            [
                new \stdClass(),
                $argumentIteratorFactory->create([
                    ['', 'notEmpty'],
                ]),
                null,
            ],
        ];
    }

    /**
     * @param mixed $input
     * @param ArgumentIterator|null $arguments
     * @param \ArrayAccess<string|int, mixed>|null $context
     *
     * @return void
     */
    #[Test]
    #[DataProvider('dataProvider_testTransform_InvalidData_Exception')]
    public function testTransform_WithInvalidData_Exception(
        mixed $input,
        ?ArgumentIterator $arguments,
        ?\ArrayAccess $context = null,
    ): void {
        $escapeHtmlTransformer = new EscapeHtml();

        $this->expectException(InvalidInputDataException::class);
        $this->expectExceptionMessage('Invalid input data for transformation');
        try {
            $escapeHtmlTransformer->transform(
                data: $input,
                arguments: $arguments,
                context: $context,
            );
        } catch (InvalidTransformationArgumentsException $exception) {
            $errors = $exception->getErrors();
            $this->assertCount(1, $errors);
            $this->assertMatchesRegularExpression(
                pattern: '/Invalid data. Expected scalar\|iterable, received .*/',
                string: $errors[0] ?? '',
            );
            $this->assertSame(
                expected: EscapeHtml::class,
                actual: $exception->getTransformerName(),
            );
            throw $exception;
        }
    }
}
