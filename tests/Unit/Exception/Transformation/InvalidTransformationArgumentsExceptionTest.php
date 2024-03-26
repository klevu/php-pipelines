<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception\Transformation;

use Klevu\Pipelines\Exception\Transformation\InvalidTransformationArgumentsException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidTransformationArgumentsException::class)]
class InvalidTransformationArgumentsExceptionTest extends TestCase
{
    /**
     * @param string $transformerName
     * @param string[] $errors
     * @param string $message
     * @param mixed[]|null $arguments
     * @return void
     */
    #[Test]
    #[TestWith(['foo', ['Error'], '', null])]
    #[TestWith([' ', ['Error', 'Error'], 'Exception with a message', ['integer']])]
    #[TestWith(['foo', [], '', ['foo' => 'bar']])]
    #[TestWith(['foo bar', ['Error Message'], '', [new \stdClass()]])]
    public function testConstruct(
        string $transformerName,
        array $errors,
        string $message,
        ?array $arguments,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new InvalidTransformationArgumentsException(
            transformerName: $transformerName,
            errors: $errors,
            arguments: $arguments,
            message: $message,
            code: 418,
            previous: $previousException,
        );

        $this->assertSame(
            expected: $transformerName,
            actual: $exception->getTransformerName(),
        );
        $this->assertSame(
            expected: $errors,
            actual: $exception->getErrors(),
        );
        $this->assertSame(
            expected: $arguments,
            actual: $exception->getArguments(),
        );
        $this->assertNull($exception->getData());
        $this->assertSame(
            expected: $message ?: 'Invalid argument for transformation',
            actual: $exception->getMessage(),
        );
        $this->assertSame(418, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
