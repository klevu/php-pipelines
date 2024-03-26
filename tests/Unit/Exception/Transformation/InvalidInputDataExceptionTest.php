<?php

/**
 * Copyright © Klevu Oy. All rights reserved. See LICENSE.txt for license details.
 */

/**
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */

declare(strict_types=1);

namespace Klevu\Pipelines\Test\Unit\Exception\Transformation;

use Klevu\Pipelines\Exception\Transformation\InvalidInputDataException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvalidInputDataException::class)]
class InvalidInputDataExceptionTest extends TestCase
{
    #[Test]
    #[TestWith([null, 'integer', ''])]
    #[TestWith(['foo', 'integer', 'Exception with a message'])]
    #[TestWith([['foo' => 'bar'], 'integer', ''])]
    #[TestWith([new \stdClass(), 'integer', ''])]
    public function testConstruct(
        mixed $data,
        string $expectedType,
        string $message,
    ): void {
        $previousException = new \Exception('Foo');
        $exception = new InvalidInputDataException(
            transformerName: 'Transformer',
            expectedType: $expectedType,
            arguments: null,
            data: $data,
            message: $message,
            code: 418,
            previous: $previousException,
        );

        $this->assertSame(
            expected: 'Transformer',
            actual: $exception->getTransformerName(),
        );
        $this->assertNull($exception->getArguments());
        $this->assertSame(
            expected: $data,
            actual: $exception->getData(),
        );
        $this->assertSame(
            expected: $message ?: 'Invalid input data for transformation',
            actual: $exception->getMessage(),
        );
        $errors = $exception->getErrors();
        $this->assertCount(1, $errors);
        $this->assertSame(
            expected: sprintf(
                'Invalid data. Expected %s, received %s',
                $expectedType,
                get_debug_type($data),
            ),
            actual: $errors[0] ?? null,
        );
        $this->assertSame(418, $exception->getCode());
        $this->assertSame($previousException, $exception->getPrevious());
    }
}
